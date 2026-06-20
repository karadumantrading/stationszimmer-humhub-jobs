<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use humhub\modules\jobs\models\JobListing;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $status string|null */

$this->title = Yii::t('JobsModule.base', 'Inserate verwalten');
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <strong><?= Html::encode($this->title) ?></strong>
        <?= Html::a(Yii::t('JobsModule.base', 'Konfiguration'), ['/jobs/admin/config'], ['class' => 'btn btn-link btn-sm pull-right']) ?>
    </div>
    <div class="panel-body">
        <p>
            <?= Html::a(Yii::t('JobsModule.base', 'Alle'), ['/jobs/admin/listing/index'], ['class' => 'btn btn-xs ' . ($status === null ? 'btn-primary' : 'btn-default')]) ?>
            <?= Html::a(Yii::t('JobsModule.base', 'status.pending_review'), ['/jobs/admin/listing/index', 'status' => JobListing::STATUS_PENDING_REVIEW], ['class' => 'btn btn-xs ' . ($status === JobListing::STATUS_PENDING_REVIEW ? 'btn-primary' : 'btn-default')]) ?>
            <?= Html::a(Yii::t('JobsModule.base', 'status.published'), ['/jobs/admin/listing/index', 'status' => JobListing::STATUS_PUBLISHED], ['class' => 'btn btn-xs ' . ($status === JobListing::STATUS_PUBLISHED ? 'btn-primary' : 'btn-default')]) ?>
        </p>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                'id',
                'title',
                'company_name',
                'canton',
                [
                    'attribute' => 'status',
                    'value' => fn($m) => Yii::t('JobsModule.base', 'status.' . $m->status),
                ],
                [
                    'attribute' => 'published_until',
                    'value' => fn($m) => $m->published_until ? substr($m->published_until, 0, 10) : '–',
                ],
                [
                    'class' => ActionColumn::class,
                    'template' => '{approve} {reject} {expire}',
                    'buttons' => [
                        'approve' => function ($url, $model) {
                            if ($model->status !== JobListing::STATUS_PENDING_REVIEW) {
                                return '';
                            }
                            return Html::a('<i class="fa fa-check"></i>', ['/jobs/admin/listing/approve', 'id' => $model->id], [
                                'title' => Yii::t('JobsModule.base', 'Freigeben'),
                                'data-method' => 'post',
                                'class' => 'text-success',
                            ]);
                        },
                        'reject' => function ($url, $model) {
                            if (!in_array($model->status, [JobListing::STATUS_PENDING_REVIEW, JobListing::STATUS_PENDING_PAYMENT], true)) {
                                return '';
                            }
                            return Html::a('<i class="fa fa-times"></i>', ['/jobs/admin/listing/reject', 'id' => $model->id], [
                                'title' => Yii::t('JobsModule.base', 'Ablehnen'),
                                'data-method' => 'post',
                                'class' => 'text-danger',
                            ]);
                        },
                        'expire' => function ($url, $model) {
                            if ($model->status !== JobListing::STATUS_PUBLISHED) {
                                return '';
                            }
                            return Html::a('<i class="fa fa-clock-o"></i>', ['/jobs/admin/listing/expire', 'id' => $model->id], [
                                'title' => Yii::t('JobsModule.base', 'Ablaufen lassen'),
                                'data-method' => 'post',
                            ]);
                        },
                    ],
                ],
            ],
        ]) ?>
    </div>
</div>
