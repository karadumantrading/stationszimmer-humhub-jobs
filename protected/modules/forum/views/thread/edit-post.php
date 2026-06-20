<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $post humhub\modules\forum\models\ForumPost */

$this->title = Yii::t('ForumModule.base', 'Beitrag bearbeiten');
?>
<div class="panel panel-default">
    <div class="panel-heading"><strong><?= Html::encode($this->title) ?></strong></div>
    <div class="panel-body">
        <?php $form = ActiveForm::begin(); ?>
        <?= $form->field($post, 'message')->textarea(['rows' => 8])->label(false) ?>
        <div class="form-group">
            <?= Html::submitButton(Yii::t('ForumModule.base', 'Speichern'), ['class' => 'btn btn-primary']) ?>
            <?= Html::a(Yii::t('ForumModule.base', 'Abbrechen'), ['/forum/thread/view', 'id' => $post->thread_id], ['class' => 'btn btn-link']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
