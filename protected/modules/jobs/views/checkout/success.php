<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $moderation bool */

$this->title = Yii::t('JobsModule.base', 'Zahlung erhalten');
?>
<div class="panel panel-default">
    <div class="panel-body text-center" style="padding:40px 20px">
        <p><i class="fa fa-check-circle text-success" style="font-size:48px"></i></p>
        <h3><?= Yii::t('JobsModule.base', 'Vielen Dank – deine Zahlung ist eingegangen.') ?></h3>
        <p class="text-muted">
            <?php if ($moderation): ?>
                <?= Yii::t('JobsModule.base', 'Dein Inserat wird nach einer kurzen Prüfung freigeschaltet.') ?>
            <?php else: ?>
                <?= Yii::t('JobsModule.base', 'Dein Inserat wird soeben veröffentlicht und ist gleich online.') ?>
            <?php endif; ?>
        </p>
        <p>
            <?= Html::a(Yii::t('JobsModule.base', 'Zu meinen Inseraten'), ['/jobs/job/my'], ['class' => 'btn btn-primary']) ?>
        </p>
    </div>
</div>
