<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use humhub\modules\jobs\models\JobListing;

/* @var $model humhub\modules\jobs\models\JobListing */

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
<?php $form = ActiveForm::begin(); ?>

<?= $form->field($model, 'company_name')->textInput(['maxlength' => true]) ?>
<?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

<div class="row">
    <div class="col-md-6"><?= $form->field($model, 'setting')->dropDownList($settingOptions, ['prompt' => Yii::t('JobsModule.base', 'Bitte wählen …')]) ?></div>
    <div class="col-md-6"><?= $form->field($model, 'canton')->dropDownList($cantonOptions, ['prompt' => Yii::t('JobsModule.base', 'Bitte wählen …')]) ?></div>
</div>

<div class="row">
    <div class="col-md-6"><?= $form->field($model, 'employment_type')->dropDownList($typeOptions, ['prompt' => Yii::t('JobsModule.base', 'Bitte wählen …')]) ?></div>
    <div class="col-md-6"><?= $form->field($model, 'location')->textInput(['maxlength' => true]) ?></div>
</div>

<div class="row">
    <div class="col-md-6"><?= $form->field($model, 'pensum_min')->input('number', ['min' => 0, 'max' => 100]) ?></div>
    <div class="col-md-6"><?= $form->field($model, 'pensum_max')->input('number', ['min' => 0, 'max' => 100]) ?></div>
</div>

<?php // @verify: Optional HumHub-RichText-Editor statt textarea (humhub\modules\content\widgets\richtext\RichTextField). ?>
<?= $form->field($model, 'description')->textarea(['rows' => 8]) ?>

<div class="row">
    <div class="col-md-6"><?= $form->field($model, 'contact_email')->textInput(['maxlength' => true]) ?></div>
    <div class="col-md-6"><?= $form->field($model, 'contact_url')->textInput(['maxlength' => true, 'placeholder' => 'https://…']) ?></div>
</div>

<div class="form-group">
    <?= Html::submitButton(Yii::t('JobsModule.base', 'Weiter zur Tarifwahl'), ['class' => 'btn btn-primary']) ?>
    <?= Html::a(Yii::t('JobsModule.base', 'Abbrechen'), ['/jobs/job/my'], ['class' => 'btn btn-link']) ?>
</div>

<?php ActiveForm::end(); ?>
