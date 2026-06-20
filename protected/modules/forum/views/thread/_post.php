<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $model humhub\modules\forum\models\ForumPost */
$author = $model->author;
// @verify: HumHub-User-API (displayName) gegen installierte Version.
$authorName = $author ? $author->displayName : Yii::t('ForumModule.base', 'Unbekannt');
?>
<div class="media" style="border-top:1px solid #eee; padding:12px 0">
    <div class="media-body">
        <div class="text-muted" style="font-size:12px">
            <strong class="text-default"><?= Html::encode($authorName) ?></strong>
            · <?= Html::encode(substr((string) $model->created_at, 0, 16)) ?>
            <?php if ($model->canManage()): ?>
                <span class="pull-right">
                    <?= Html::a(Yii::t('ForumModule.base', 'Bearbeiten'), ['/forum/thread/edit-post', 'id' => $model->id], ['class' => 'text-muted']) ?>
                    ·
                    <?= Html::a(Yii::t('ForumModule.base', 'Löschen'), ['/forum/thread/delete-post', 'id' => $model->id],
                        ['class' => 'text-danger', 'data-method' => 'post',
                         'data-confirm' => Yii::t('ForumModule.base', 'Beitrag löschen?')]) ?>
                </span>
            <?php endif; ?>
        </div>
        <div style="margin-top:6px; white-space:pre-line"><?= nl2br(Html::encode($model->message)) ?></div>
    </div>
</div>
