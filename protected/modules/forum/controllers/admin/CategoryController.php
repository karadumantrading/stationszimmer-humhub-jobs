<?php

namespace humhub\modules\forum\controllers\admin;

use Yii;
use yii\web\NotFoundHttpException;
use humhub\modules\admin\components\Controller;
use humhub\modules\forum\models\ForumCategory;

/**
 * Bereichs-Verwaltung (Admin).
 *
 * @verify: humhub\modules\admin\components\Controller erzwingt Admin-Rechte.
 */
class CategoryController extends Controller
{
    public function actionIndex()
    {
        $categories = ForumCategory::find()->orderBy(['sort_order' => SORT_ASC])->all();
        return $this->render('@forum/views/admin/category-index', ['categories' => $categories]);
    }

    public function actionEdit($id = null)
    {
        $category = $id ? ForumCategory::findOne((int) $id) : new ForumCategory();
        if ($category === null) {
            throw new NotFoundHttpException();
        }

        if ($category->load(Yii::$app->request->post()) && $category->save()) {
            $this->view->saved();
            return $this->redirect(['/forum/admin/category/index']);
        }

        return $this->render('@forum/views/admin/category-form', ['model' => $category]);
    }

    public function actionDelete($id)
    {
        $this->forcePostRequest();
        $category = ForumCategory::findOne((int) $id);
        if ($category !== null) {
            // Cascade entfernt Themen + Beiträge dieses Bereichs.
            $category->delete();
            $this->view->success(Yii::t('ForumModule.base', 'Bereich gelöscht.'));
        }
        return $this->redirect(['/forum/admin/category/index']);
    }
}
