<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ListView;

/* @var $this yii\web\View */
/* @var $category humhub\modules\forum\models\ForumCategory */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = $category->title;
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <a href="<?= Url::to(['/forum/category/index']) ?>" class="text-muted">&laquo; <?= Yii::t('ForumModule.base', 'Forum') ?></a>
        <strong style="margin-left:8px"><?= Html::encode($category->title) ?></strong>
        <?php if (!Yii::$app->user->isGuest): ?>
            <?= Html::a('<i class="fa fa-plus"></i> ' . Yii::t('ForumModule.base', 'Neues Thema'),
                ['/forum/thread/create', 'category' => $category->id], ['class' => 'btn btn-primary btn-xs pull-right']) ?>
        <?php endif; ?>
    </div>
    <div class="panel-body">
        <?php if ($category->description): ?>
            <p class="text-muted"><?= Html::encode($category->description) ?></p>
        <?php endif; ?>

        <?= ListView::widget([
            'dataProvider' => $dataProvider,
            'itemView' => '_thread_row',
            'layout' => "{items}\n{pager}",
            'options' => ['class' => 'list-group'],
            'itemOptions' => ['tag' => false],
            'emptyText' => '<p class="text-muted">' . Yii::t('ForumModule.base', 'Noch keine Themen – starte das erste!') . '</p>',
        ]) ?>
    </div>
</div>
