<?php

use yii\helpers\Html;
use yii\widgets\ListView;
use humhub\modules\jobs\models\JobListing;
use humhub\modules\jobs\assets\Assets;

/* @var $this yii\web\View */
/* @var $searchModel humhub\modules\jobs\models\JobListingSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

Assets::register($this);
$this->title = Yii::t('JobsModule.base', 'Jobbörse');

// Beschriftete Optionen aus den Whitelists.
$settingOptions = [];
foreach (JobListing::SETTINGS as $s) {
    $settingOptions[$s] = Yii::t('JobsModule.base', 'setting.' . $s);
}
$typeOptions = [];
foreach (JobListing::EMPLOYMENT_TYPES as $t) {
    $typeOptions[$t] = Yii::t('JobsModule.base', 'type.' . $t);
}
$cantonOptions = array_combine(JobListing::CANTONS, JobListing::CANTONS);
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <strong><i class="fa fa-briefcase"></i> <?= Html::encode($this->title) ?></strong>
        <span class="pull-right">
            <?= Html::a(Yii::t('JobsModule.base', 'Meine Inserate'), ['/jobs/job/my'], ['class' => 'btn btn-link btn-sm']) ?>
            <?= Html::a('<i class="fa fa-plus"></i> ' . Yii::t('JobsModule.base', 'Inserat aufgeben'), ['/jobs/job/create'], ['class' => 'btn btn-primary btn-sm']) ?>
        </span>
    </div>
    <div class="panel-body">
        <?= Html::beginForm(['/jobs/job/index'], 'get', ['class' => 'form-inline jobs-filter']) ?>
        <?= Html::dropDownList('JobListingSearch[canton]', $searchModel->canton, ['' => Yii::t('JobsModule.base', 'Alle Kantone')] + $cantonOptions, ['class' => 'form-control input-sm']) ?>
        <?= Html::dropDownList('JobListingSearch[setting]', $searchModel->setting, ['' => Yii::t('JobsModule.base', 'Alle Bereiche')] + $settingOptions, ['class' => 'form-control input-sm']) ?>
        <?= Html::dropDownList('JobListingSearch[employment_type]', $searchModel->employment_type, ['' => Yii::t('JobsModule.base', 'Alle Anstellungen')] + $typeOptions, ['class' => 'form-control input-sm']) ?>
        <?= Html::input('number', 'JobListingSearch[pensum]', $searchModel->pensum, ['class' => 'form-control input-sm', 'placeholder' => Yii::t('JobsModule.base', 'Pensum %'), 'min' => 0, 'max' => 100, 'style' => 'width:110px']) ?>
        <?= Html::submitButton(Yii::t('JobsModule.base', 'Filtern'), ['class' => 'btn btn-default btn-sm']) ?>
        <?= Html::a(Yii::t('JobsModule.base', 'Zurücksetzen'), ['/jobs/job/index'], ['class' => 'btn btn-link btn-sm']) ?>
        <?= Html::endForm() ?>
    </div>
</div>

<?= ListView::widget([
    'dataProvider' => $dataProvider,
    'itemView' => '_card',
    'layout' => "{items}\n{pager}",
    'emptyText' => '<div class="panel panel-default"><div class="panel-body text-center text-muted">'
        . Yii::t('JobsModule.base', 'Aktuell sind keine Inserate ausgeschrieben.') . '</div></div>',
]) ?>
