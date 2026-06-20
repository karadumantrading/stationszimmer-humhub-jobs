<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $model humhub\modules\forum\models\ForumThread */
?>
<a href="<?= Url::to(['/forum/thread/view', 'id' => $model->id]) ?>" class="list-group-item">
    <span class="pull-right text-muted" style="white-space:nowrap">
        <?= $model->getReplyCount() ?> <?= Yii::t('ForumModule.base', 'Antworten') ?>
    </span>
    <?php if ($model->is_pinned): ?><i class="fa fa-thumb-tack text-warning" title="<?= Yii::t('ForumModule.base', 'Angepinnt') ?>"></i> <?php endif; ?>
    <?php if ($model->is_locked): ?><i class="fa fa-lock text-muted" title="<?= Yii::t('ForumModule.base', 'Geschlossen') ?>"></i> <?php endif; ?>
    <strong><?= Html::encode($model->title) ?></strong>
    <?php if ($model->last_post_at): ?>
        <br><small class="text-muted"><?= Yii::t('ForumModule.base', 'Letzte Aktivität') ?>: <?= Html::encode(substr($model->last_post_at, 0, 16)) ?></small>
    <?php endif; ?>
</a>
