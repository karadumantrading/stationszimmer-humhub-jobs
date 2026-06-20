<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model humhub\modules\jobs\models\JobListing */

$isNew = $model->isNewRecord;
$this->title = $isNew
    ? Yii::t('JobsModule.base', 'Neues Inserat')
    : Yii::t('JobsModule.base', 'Inserat bearbeiten');
?>
<div class="panel panel-default">
    <div class="panel-heading"><strong><?= Html::encode($this->title) ?></strong></div>
    <div class="panel-body">
        <?php if ($isNew): ?>
            <p class="text-muted">
                <?= Yii::t('JobsModule.base', 'Im nächsten Schritt wählst du den Tarif. Veröffentlicht wird das Inserat nach der Bezahlung (Lehrstellen sind gratis).') ?>
            </p>
        <?php endif; ?>
        <?= $this->render('_form', ['model' => $model]) ?>
    </div>
</div>
