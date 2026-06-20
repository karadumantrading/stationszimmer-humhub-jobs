<?php

use yii\helpers\Html;

/* @var $model humhub\modules\jobs\models\JobListing */
$pensum = '';
if ($model->pensum_min !== null || $model->pensum_max !== null) {
    $pensum = ($model->pensum_min !== null && $model->pensum_max !== null && $model->pensum_min !== $model->pensum_max)
        ? "{$model->pensum_min}–{$model->pensum_max}%"
        : (($model->pensum_min ?? $model->pensum_max) . '%');
}
?>
<div class="panel panel-default jobs-card<?= $model->is_top ? ' jobs-card-top' : '' ?>">
    <div class="panel-body">
        <?php if ($model->is_top): ?>
            <span class="label label-warning pull-right"><i class="fa fa-star"></i> <?= Yii::t('JobsModule.base', 'Top') ?></span>
        <?php endif; ?>
        <h4 style="margin-top:0">
            <?= Html::a(Html::encode($model->title), ['/jobs/job/view', 'id' => $model->id]) ?>
        </h4>
        <div class="text-muted">
            <strong><?= Html::encode($model->company_name) ?></strong>
        </div>
        <div class="text-muted" style="margin-top:6px">
            <i class="fa fa-map-marker"></i>
            <?= Html::encode(($model->location ? $model->location . ', ' : '') . 'Kanton ' . $model->canton) ?>
            &nbsp;·&nbsp;
            <?= Html::encode(Yii::t('JobsModule.base', 'setting.' . $model->setting)) ?>
            &nbsp;·&nbsp;
            <?= Html::encode(Yii::t('JobsModule.base', 'type.' . $model->employment_type)) ?>
            <?php if ($pensum !== ''): ?>&nbsp;·&nbsp;<?= Html::encode($pensum) ?><?php endif; ?>
        </div>
    </div>
</div>
