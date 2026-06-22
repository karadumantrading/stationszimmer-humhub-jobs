<?php

namespace humhub\modules\forum\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use humhub\components\Controller;
use humhub\components\access\ControllerAccess;
use humhub\modules\forum\models\ForumCategory;
use humhub\modules\forum\models\ForumThread;

/**
 * Bereichsübersicht und Themenliste je Bereich (öffentlich lesbar).
 *
 * Gast-Lesezugriff via 'guestAccess'-Regel (greift, wenn global «Gastzugriff»
 * aktiviert ist – Administration → Einstellungen → Allgemein).
 */
class CategoryController extends Controller
{
    public $access = ControllerAccess::class;

    protected function getAccessRules(): array
    {
        return [
            ['guestAccess' => ['index', 'view']],
        ];
    }

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
