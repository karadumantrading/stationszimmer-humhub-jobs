<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $thread humhub\modules\forum\models\ForumThread */
/* @var $post humhub\modules\forum\models\ForumPost */
/* @var $category humhub\modules\forum\models\ForumCategory */

$this->title = Yii::t('ForumModule.base', 'Neues Thema');
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <strong><?= Html::encode($this->title) ?></strong>
        <span class="text-muted">· <?= Html::encode($category->title) ?></span>
    </div>
    <div class="panel-body">
        <?php $form = ActiveForm::begin(); ?>
        <?= $form->field($thread, 'title')->textInput(['maxlength' => true, 'placeholder' => Yii::t('ForumModule.base', 'Worum geht es?')]) ?>
        <?= $form->field($post, 'message')->textarea(['rows' => 8, 'placeholder' => Yii::t('ForumModule.base', 'Schreibe deinen Beitrag …')]) ?>
        <div class="form-group">
            <?= Html::submitButton(Yii::t('ForumModule.base', 'Thema erstellen'), ['class' => 'btn btn-primary']) ?>
            <?= Html::a(Yii::t('ForumModule.base', 'Abbrechen'), ['/forum/category/view', 'id' => $category->id], ['class' => 'btn btn-link']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
