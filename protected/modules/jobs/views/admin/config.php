<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model humhub\modules\jobs\models\forms\ConfigForm */

$this->title = Yii::t('JobsModule.base', 'Jobbörse – Konfiguration');
?>
<div class="panel panel-default">
    <div class="panel-heading"><strong><?= Html::encode($this->title) ?></strong></div>
    <div class="panel-body">

        <div class="alert alert-info">
            <?= Yii::t('JobsModule.base', 'Der Stripe Secret Key und das Webhook-Secret gehören NICHT hierher, sondern in die Server-Konfiguration (params/.env). Hier nur die Price-IDs und Einstellungen.') ?>
        </div>

        <?php $form = ActiveForm::begin(); ?>

        <h4><?= Yii::t('JobsModule.base', 'Tarife (Stripe-Price-IDs)') ?></h4>
        <?= $form->field($model, 'stripePriceIntro')->textInput(['placeholder' => 'price_…']) ?>
        <?= $form->field($model, 'stripePriceBasis')->textInput(['placeholder' => 'price_…']) ?>
        <?= $form->field($model, 'stripePriceTop')->textInput(['placeholder' => 'price_…']) ?>

        <hr>
        <h4><?= Yii::t('JobsModule.base', 'Einstellungen') ?></h4>
        <?= $form->field($model, 'introListingLimit')->input('number', ['min' => 0])
            ->hint(Yii::t('JobsModule.base', 'Solange weniger als so viele bezahlte Inserate veröffentlicht wurden, gilt der vergünstigte Intro-Tarif. Standard: 50.')) ?>
        <?= $form->field($model, 'durationDays')->input('number', ['min' => 1, 'max' => 365]) ?>
        <?= $form->field($model, 'moderationEnabled')->checkbox() ?>

        <div class="form-group">
            <?= Html::submitButton(Yii::t('JobsModule.base', 'Speichern'), ['class' => 'btn btn-primary']) ?>
            <?= Html::a(Yii::t('JobsModule.base', 'Inserate verwalten'), ['/jobs/admin/listing/index'], ['class' => 'btn btn-default']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
