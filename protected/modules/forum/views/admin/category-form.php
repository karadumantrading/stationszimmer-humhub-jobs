<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model humhub\modules\forum\models\ForumCategory */

$this->title = $model->isNewRecord
    ? Yii::t('ForumModule.base', 'Neuer Bereich')
    : Yii::t('ForumModule.base', 'Bereich bearbeiten');
?>
<div class="panel panel-default">
    <div class="panel-heading"><strong><?= Html::encode($this->title) ?></strong></div>
    <div class="panel-body">
        <?php $form = ActiveForm::begin(); ?>
        <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'slug')->textInput(['maxlength' => true, 'placeholder' => 'z. B. spital']) ?>
        <?= $form->field($model, 'description')->textarea(['rows' => 2]) ?>
        <div class="row">
            <div class="col-md-6"><?= $form->field($model, 'icon')->textInput(['placeholder' => 'z. B. hospital-o']) ?></div>
            <div class="col-md-6"><?= $form->field($model, 'sort_order')->input('number') ?></div>
        </div>
        <div class="form-group">
            <?= Html::submitButton(Yii::t('ForumModule.base', 'Speichern'), ['class' => 'btn btn-primary']) ?>
            <?= Html::a(Yii::t('ForumModule.base', 'Abbrechen'), ['/forum/admin/category/index'], ['class' => 'btn btn-link']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
