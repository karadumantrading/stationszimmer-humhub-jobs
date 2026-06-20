<?php

namespace humhub\modules\jobs\controllers\admin;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use humhub\modules\admin\components\Controller;
use humhub\modules\jobs\models\JobListing;

/**
 * Inserate-Verwaltung & Moderation (Admin).
 */
class ListingController extends Controller
{
    /** Alle Inserate, optional nach Status gefiltert. */
    public function actionIndex($status = null)
    {
        $query = JobListing::find()->orderBy(['created_at' => SORT_DESC]);
        if ($status !== null && in_array($status, JobListing::STATUSES, true)) {
            $query->andWhere(['status' => $status]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 30],
        ]);

        return $this->render('@jobs/views/admin/listing-index', [
            'dataProvider' => $dataProvider,
            'status' => $status,
        ]);
    }

    /** Freigeben (aus Moderation/Review heraus veröffentlichen). */
    public function actionApprove($id)
    {
        $this->forcePostRequest();
        $listing = $this->find((int) $id);
        $listing->publish();
        $this->view->success(Yii::t('JobsModule.base', 'Inserat freigegeben und veröffentlicht.'));
        return $this->redirect(['/jobs/admin/listing/index']);
    }

    /** Ablehnen. */
    public function actionReject($id)
    {
        $this->forcePostRequest();
        $listing = $this->find((int) $id);
        $listing->updateAttributes(['status' => JobListing::STATUS_REJECTED, 'updated_at' => date('Y-m-d H:i:s')]);
        $this->view->success(Yii::t('JobsModule.base', 'Inserat abgelehnt.'));
        return $this->redirect(['/jobs/admin/listing/index']);
    }

    /** Manuell ablaufen lassen. */
    public function actionExpire($id)
    {
        $this->forcePostRequest();
        $listing = $this->find((int) $id);
        $listing->updateAttributes(['status' => JobListing::STATUS_EXPIRED, 'updated_at' => date('Y-m-d H:i:s')]);
        $this->view->success(Yii::t('JobsModule.base', 'Inserat auf «abgelaufen» gesetzt.'));
        return $this->redirect(['/jobs/admin/listing/index']);
    }

    private function find(int $id): JobListing
    {
        $listing = JobListing::findOne($id);
        if ($listing === null) {
            throw new NotFoundHttpException(Yii::t('JobsModule.base', 'Inserat nicht gefunden.'));
        }
        return $listing;
    }
}
