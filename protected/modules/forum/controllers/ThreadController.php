<?php

namespace humhub\modules\forum\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use humhub\components\Controller;
use humhub\components\access\ControllerAccess;
use humhub\modules\forum\Module;
use humhub\modules\forum\models\ForumCategory;
use humhub\modules\forum\models\ForumThread;
use humhub\modules\forum\models\ForumPost;

/**
 * Themen ansehen/erstellen/beantworten + Moderation (anpinnen/sperren/löschen).
 */
class ThreadController extends Controller
{
    public $access = ControllerAccess::class;

    protected function getAccessRules(): array
    {
        return [[
            ControllerAccess::RULE_LOGGED_IN_ONLY => [
                'create', 'reply', 'edit-post', 'delete-post', 'delete', 'pin', 'lock',
            ],
        ]];
    }

    /** Thema mit Beiträgen (öffentlich) + Antwortfeld (eingeloggt, wenn offen). */
    public function actionView($id)
    {
        $thread = $this->findThread((int) $id);

        $dataProvider = new ActiveDataProvider([
            'query' => ForumPost::find()->where(['thread_id' => $thread->id])->orderBy(['created_at' => SORT_ASC]),
            'pagination' => ['pageSize' => 20],
        ]);

        $reply = new ForumPost();
        return $this->render('view', [
            'thread' => $thread,
            'dataProvider' => $dataProvider,
            'reply' => $reply,
        ]);
    }

    /** Neues Thema in einem Bereich (Titel + Eröffnungsbeitrag). */
    public function actionCreate($category)
    {
        $cat = ForumCategory::findOne((int) $category);
        if ($cat === null) {
            throw new NotFoundHttpException(Yii::t('ForumModule.base', 'Bereich nicht gefunden.'));
        }

        $thread = new ForumThread(['scenario' => ForumThread::SCENARIO_CREATE]);
        $post = new ForumPost();

        if (Yii::$app->request->isPost
            && $thread->load(Yii::$app->request->post())
            && $post->load(Yii::$app->request->post())
            && $thread->validate(['title'])
            && $post->validate(['message'])) {

            $tx = Yii::$app->db->beginTransaction();
            try {
                $thread->category_id = $cat->id;
                $thread->created_by = (int) Yii::$app->user->id;
                $thread->lang = Yii::$app->language === 'fr' ? 'fr' : 'de';
                $thread->save(false);

                $post->thread_id = $thread->id;
                $post->created_by = (int) Yii::$app->user->id;
                $post->save(false);

                $thread->touchLastPost();
                $tx->commit();
            } catch (\Throwable $e) {
                $tx->rollBack();
                throw $e;
            }

            return $this->redirect(['/forum/thread/view', 'id' => $thread->id]);
        }

        return $this->render('create', ['thread' => $thread, 'post' => $post, 'category' => $cat]);
    }

    /** Antwort auf ein Thema. */
    public function actionReply($id)
    {
        $this->forcePostRequest();
        $thread = $this->findThread((int) $id);

        if ($thread->is_locked && !Module::canModerate()) {
            throw new ForbiddenHttpException(Yii::t('ForumModule.base', 'Dieses Thema ist geschlossen.'));
        }

        $post = new ForumPost();
        if ($post->load(Yii::$app->request->post()) && $post->validate(['message'])) {
            $post->thread_id = $thread->id;
            $post->created_by = (int) Yii::$app->user->id;
            $post->save(false);
            $thread->touchLastPost();
        }

        return $this->redirect(['/forum/thread/view', 'id' => $thread->id]);
    }

    /** Eigenen Beitrag bearbeiten. */
    public function actionEditPost($id)
    {
        $post = ForumPost::findOne((int) $id);
        if ($post === null) {
            throw new NotFoundHttpException(Yii::t('ForumModule.base', 'Beitrag nicht gefunden.'));
        }
        if (!$post->canManage()) {
            throw new ForbiddenHttpException(Yii::t('ForumModule.base', 'Keine Berechtigung.'));
        }

        if ($post->load(Yii::$app->request->post()) && $post->validate(['message'])) {
            $post->save(false);
            return $this->redirect(['/forum/thread/view', 'id' => $post->thread_id]);
        }

        return $this->render('edit-post', ['post' => $post]);
    }

    /** Beitrag löschen. Eröffnungsbeitrag => ganzes Thema. */
    public function actionDeletePost($id)
    {
        $this->forcePostRequest();
        $post = ForumPost::findOne((int) $id);
        if ($post === null) {
            throw new NotFoundHttpException(Yii::t('ForumModule.base', 'Beitrag nicht gefunden.'));
        }
        if (!$post->canManage()) {
            throw new ForbiddenHttpException(Yii::t('ForumModule.base', 'Keine Berechtigung.'));
        }

        $threadId = (int) $post->thread_id;
        if ($post->isOpeningPost()) {
            // Eröffnungsbeitrag -> gesamtes Thema entfernen (Cascade löscht Beiträge).
            $thread = ForumThread::findOne($threadId);
            $categoryId = $thread ? (int) $thread->category_id : null;
            if ($thread) {
                $thread->delete();
            }
            $this->view->success(Yii::t('ForumModule.base', 'Thema gelöscht.'));
            return $categoryId
                ? $this->redirect(['/forum/category/view', 'id' => $categoryId])
                : $this->redirect(['/forum/category/index']);
        }

        $post->delete();
        return $this->redirect(['/forum/thread/view', 'id' => $threadId]);
    }

    /** Ganzes Thema löschen (Owner/Moderation). */
    public function actionDelete($id)
    {
        $this->forcePostRequest();
        $thread = $this->findThread((int) $id);
        if (!$thread->canManage()) {
            throw new ForbiddenHttpException(Yii::t('ForumModule.base', 'Keine Berechtigung.'));
        }
        $categoryId = (int) $thread->category_id;
        $thread->delete();
        $this->view->success(Yii::t('ForumModule.base', 'Thema gelöscht.'));
        return $this->redirect(['/forum/category/view', 'id' => $categoryId]);
    }

    /** Anpinnen umschalten (Moderation). */
    public function actionPin($id)
    {
        $this->forcePostRequest();
        $this->moderate((int) $id, fn(ForumThread $t) => $t->updateAttributes(['is_pinned' => $t->is_pinned ? 0 : 1]));
        return $this->redirect(['/forum/thread/view', 'id' => (int) $id]);
    }

    /** Sperren umschalten (Moderation). */
    public function actionLock($id)
    {
        $this->forcePostRequest();
        $this->moderate((int) $id, fn(ForumThread $t) => $t->updateAttributes(['is_locked' => $t->is_locked ? 0 : 1]));
        return $this->redirect(['/forum/thread/view', 'id' => (int) $id]);
    }

    private function moderate(int $id, callable $fn): void
    {
        if (!Module::canModerate()) {
            throw new ForbiddenHttpException(Yii::t('ForumModule.base', 'Keine Berechtigung.'));
        }
        $fn($this->findThread($id));
    }

    private function findThread(int $id): ForumThread
    {
        $thread = ForumThread::findOne($id);
        if ($thread === null) {
            throw new NotFoundHttpException(Yii::t('ForumModule.base', 'Thema nicht gefunden.'));
        }
        return $thread;
    }
}
