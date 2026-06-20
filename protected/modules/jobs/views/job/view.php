<?php

use yii\helpers\Html;
use humhub\modules\jobs\models\JobListing;
use humhub\modules\jobs\assets\Assets;

/* @var $this yii\web\View */
/* @var $listing humhub\modules\jobs\models\JobListing */

Assets::register($this);
$this->title = $listing->title;
$pensum = '';
if ($listing->pensum_min !== null || $listing->pensum_max !== null) {
    $pensum = ($listing->pensum_min !== null && $listing->pensum_max !== null && $listing->pensum_min !== $listing->pensum_max)
        ? "{$listing->pensum_min}–{$listing->pensum_max}%"
        : (($listing->pensum_min ?? $listing->pensum_max) . '%');
}
?>
<div class="panel panel-default">
    <div class="panel-body">
        <?= Html::a('&laquo; ' . Yii::t('JobsModule.base', 'Zur Jobbörse'), ['/jobs/job/index'], ['class' => 'btn btn-link btn-sm pull-right']) ?>

        <?php if ($listing->is_top): ?>
            <span class="label label-warning"><i class="fa fa-star"></i> <?= Yii::t('JobsModule.base', 'Top') ?></span>
        <?php endif; ?>
        <?php if (!$listing->isActive()): ?>
            <span class="label label-default"><?= Html::encode(Yii::t('JobsModule.base', 'status.' . $listing->status)) ?></span>
        <?php endif; ?>

        <h2 style="margin-top:10px"><?= Html::encode($listing->title) ?></h2>
        <p class="lead" style="margin-bottom:5px"><?= Html::encode($listing->company_name) ?></p>
        <p class="text-muted">
            <i class="fa fa-map-marker"></i>
            <?= Html::encode(($listing->location ? $listing->location . ', ' : '') . 'Kanton ' . $listing->canton) ?>
            &nbsp;·&nbsp; <?= Html::encode(Yii::t('JobsModule.base', 'setting.' . $listing->setting)) ?>
            &nbsp;·&nbsp; <?= Html::encode(Yii::t('JobsModule.base', 'type.' . $listing->employment_type)) ?>
            <?php if ($pensum !== ''): ?>&nbsp;·&nbsp; <?= Html::encode($pensum) ?><?php endif; ?>
        </p>

        <hr>
        <div class="jobs-description"><?= nl2br(Html::encode($listing->description)) ?></div>

        <?php if ($listing->contact_email || $listing->contact_url): ?>
            <hr>
            <h4><?= Yii::t('JobsModule.base', 'Bewerbung & Kontakt') ?></h4>
            <?php if ($listing->contact_email): ?>
                <?= Html::a('<i class="fa fa-envelope"></i> ' . Yii::t('JobsModule.base', 'Per E-Mail bewerben'),
                    'mailto:' . Html::encode($listing->contact_email), ['class' => 'btn btn-primary']) ?>
            <?php endif; ?>
            <?php if ($listing->contact_url): ?>
                <?= Html::a('<i class="fa fa-external-link"></i> ' . Yii::t('JobsModule.base', 'Zur Ausschreibung'),
                    Html::encode($listing->contact_url), ['class' => 'btn btn-default', 'target' => '_blank', 'rel' => 'noopener noreferrer']) ?>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($listing->canManage()): ?>
            <hr>
            <?= Html::a(Yii::t('JobsModule.base', 'Bearbeiten'), ['/jobs/job/edit', 'id' => $listing->id], ['class' => 'btn btn-default btn-sm']) ?>
            <?php if ($listing->status === JobListing::STATUS_DRAFT): ?>
                <?= Html::a('<i class="fa fa-credit-card"></i> ' . Yii::t('JobsModule.base', 'Tarif wählen & veröffentlichen'),
                    ['/jobs/checkout/select', 'id' => $listing->id], ['class' => 'btn btn-success btn-sm']) ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
