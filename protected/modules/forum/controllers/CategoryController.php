<?php

namespace humhub\modules\forum\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use humhub\components\Controller;
use humhub\modules\forum\models\ForumCategory;
use humhub\modules\forum\models\ForumThread;

/**
 * Bereichsübersicht und Themenliste je Bereich (öffentlich lesbar).
 *
 * @verify: Controller/Access gegen installierte HumHub-Version; Guest-Sicht
 * hängt zusätzlich an der HumHub-Einstellung «Zugriff für Gäste».
 */
class CategoryController extends Controller
{
    /** Alle Bereiche. */
    public function actionIndex()
    {
        $categories = ForumCategory::find()->orderBy(['sort_order' => SORT_ASC])->all();
        return $this->render('index', ['categories' => $categories]);
    }

    /** Themen eines Bereichs (angepinnt zuerst, dann letzte Aktivität). */
    public function actionView($id)
    {
        $category = ForumCategory::findOne((int) $id);
        if ($category === null) {
            throw new NotFoundHttpException(Yii::t('ForumModule.base', 'Bereich nicht gefunden.'));
        }

        $dataProvider = new ActiveDataProvider([
            'query' => ForumThread::find()->where(['category_id' => $category->id]),
            'sort' => [
                'defaultOrder' => ['is_pinned' => SORT_DESC, 'last_post_at' => SORT_DESC],
                'attributes' => ['last_post_at', 'is_pinned'],
            ],
            'pagination' => ['pageSize' => 25],
        ]);

        return $this->render('view', [
            'category' => $category,
            'dataProvider' => $dataProvider,
        ]);
    }
}
