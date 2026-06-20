<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $categories humhub\modules\forum\models\ForumCategory[] */

$this->title = Yii::t('ForumModule.base', 'Forum – Bereiche');
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <strong><?= Html::encode($this->title) ?></strong>
        <?= Html::a('<i class="fa fa-plus"></i> ' . Yii::t('ForumModule.base', 'Neuer Bereich'), ['/forum/admin/category/edit'], ['class' => 'btn btn-primary btn-xs pull-right']) ?>
    </div>
    <div class="panel-body">
        <table class="table table-hover">
            <thead>
            <tr>
                <th><?= Yii::t('ForumModule.base', 'Sortierung') ?></th>
                <th><?= Yii::t('ForumModule.base', 'Titel') ?></th>
                <th><?= Yii::t('ForumModule.base', 'Kürzel (slug)') ?></th>
                <th><?= Yii::t('ForumModule.base', 'Themen') ?></th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($categories as $cat): ?>
                <tr>
                    <td><?= (int) $cat->sort_order ?></td>
                    <td><?= Html::encode($cat->title) ?></td>
                    <td><code><?= Html::encode($cat->slug) ?></code></td>
                    <td><?= $cat->getThreadCount() ?></td>
                    <td class="text-right">
                        <?= Html::a(Yii::t('ForumModule.base', 'Bearbeiten'), ['/forum/admin/category/edit', 'id' => $cat->id], ['class' => 'btn btn-default btn-xs']) ?>
                        <?= Html::a(Yii::t('ForumModule.base', 'Löschen'), ['/forum/admin/category/delete', 'id' => $cat->id],
                            ['class' => 'btn btn-danger btn-xs', 'data-method' => 'post',
                             'data-confirm' => Yii::t('ForumModule.base', 'Bereich inkl. aller Themen löschen?')]) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
