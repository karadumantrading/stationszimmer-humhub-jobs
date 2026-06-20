<?php

namespace humhub\modules\jobs\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use humhub\components\Controller;
use humhub\components\access\ControllerAccess;
use humhub\modules\jobs\models\JobListing;
use humhub\modules\jobs\models\JobListingSearch;
use humhub\modules\jobs\models\forms\JobListingForm;

/**
 * Öffentliche Inserateliste + Erfassung/Verwaltung eigener Inserate.
 *
 * @verify: humhub\components\Controller / ControllerAccess gegen installierte Version.
 * Guest-Sichtbarkeit der Liste hängt zusätzlich von der HumHub-Einstellung
 * «Eingeschränkter Zugriff für Gäste» ab.
 */
class JobController extends Controller
{
    public $access = ControllerAccess::class;

    protected function getAccessRules(): array
    {
        return [
            [ControllerAccess::RULE_LOGGED_IN_ONLY => ['create', 'edit', 'my']],
        ];
    }

    /** Öffentliche, filterbare Liste aktiver Inserate. */
    public function actionIndex()
    {
        $searchModel = new JobListingSearch();
        $dataProvider = $searchModel->searchPublished(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /** Detailseite. Aktive Inserate für alle; eigene/Entwürfe nur für Owner/Admin. */
    public function actionView($id)
    {
        $listing = $this->findListing((int) $id);

        if (!$listing->isActive() && !$listing->canManage()) {
            throw new ForbiddenHttpException(Yii::t('JobsModule.base', 'Dieses Inserat ist nicht (mehr) öffentlich.'));
        }

        return $this->render('view', ['listing' => $listing]);
    }

    /** Neues Inserat als Entwurf erfassen → danach Tarifwahl/Bezahlung. */
    public function actionCreate()
    {
        $form = new JobListingForm();

        if ($form->load(Yii::$app->request->post())) {
            $form->created_by = (int) Yii::$app->user->id;
            $form->status = JobListing::STATUS_DRAFT;
            if ($form->save()) {
                return $this->redirect(['/jobs/checkout/select', 'id' => $form->id]);
            }
        }

        return $this->render('create', ['model' => $form]);
    }

    /** Eigenes Inserat bearbeiten. */
    public function actionEdit($id)
    {
        $listing = $this->findListing((int) $id);
        $this->ensureManage($listing);

        // Edit-Szenario erzwingen (keine Status-/Tarif-/Stripe-Massenzuweisung).
        $listing->scenario = JobListing::SCENARIO_EMPLOYER;

        if ($listing->load(Yii::$app->request->post()) && $listing->save()) {
            $this->view->success(Yii::t('JobsModule.base', 'Inserat gespeichert.'));
            return $this->redirect(['/jobs/job/view', 'id' => $listing->id]);
        }

        return $this->render('create', ['model' => $listing]);
    }

    /** «Meine Inserate» (alle Status). */
    public function actionMy()
    {
        $listings = JobListing::find()
            ->where(['created_by' => (int) Yii::$app->user->id])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        return $this->render('my', ['listings' => $listings]);
    }

    private function findListing(int $id): JobListing
    {
        $listing = JobListing::findOne($id);
        if ($listing === null) {
            throw new NotFoundHttpException(Yii::t('JobsModule.base', 'Inserat nicht gefunden.'));
        }
        return $listing;
    }

    private function ensureManage(JobListing $listing): void
    {
        if (!$listing->canManage()) {
            throw new ForbiddenHttpException(Yii::t('JobsModule.base', 'Keine Berechtigung für dieses Inserat.'));
        }
    }
}
