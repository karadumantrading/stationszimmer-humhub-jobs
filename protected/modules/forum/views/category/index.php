<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $categories humhub\modules\forum\models\ForumCategory[] */

$this->title = Yii::t('ForumModule.base', 'Forum');
?>
<div class="panel panel-default">
    <div class="panel-heading"><strong><i class="fa fa-comments"></i> <?= Html::encode($this->title) ?></strong></div>
    <div class="list-group" style="margin-bottom:0">
        <?php foreach ($categories as $cat): ?>
            <?= Html::beginTag('a', ['href' => \yii\helpers\Url::to(['/forum/category/view', 'id' => $cat->id]), 'class' => 'list-group-item']) ?>
            <div class="media">
                <div class="media-left">
                    <i class="fa fa-<?= Html::encode($cat->icon ?: 'folder') ?> fa-fw" style="font-size:20px"></i>
                </div>
                <div class="media-body">
                    <h4 class="list-group-item-heading" style="margin:0"><?= Html::encode($cat->title) ?></h4>
                    <p class="list-group-item-text text-muted" style="margin:2px 0 0"><?= Html::encode($cat->description) ?></p>
                </div>
                <div class="media-right text-muted" style="white-space:nowrap">
                    <?= $cat->getThreadCount() ?> <?= Yii::t('ForumModule.base', 'Themen') ?>
                </div>
            </div>
            <?= Html::endTag('a') ?>
        <?php endforeach; ?>
    </div>
</div>
