<?php

use yii\helpers\Html;
use humhub\modules\jobs\models\JobListing;

/* @var $this yii\web\View */
/* @var $listings humhub\modules\jobs\models\JobListing[] */

$this->title = Yii::t('JobsModule.base', 'Meine Inserate');
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <strong><?= Html::encode($this->title) ?></strong>
        <?= Html::a('<i class="fa fa-plus"></i> ' . Yii::t('JobsModule.base', 'Inserat aufgeben'), ['/jobs/job/create'], ['class' => 'btn btn-primary btn-sm pull-right']) ?>
    </div>
    <div class="panel-body">
        <?php if (empty($listings)): ?>
            <p class="text-muted"><?= Yii::t('JobsModule.base', 'Du hast noch keine Inserate erstellt.') ?></p>
        <?php else: ?>
            <table class="table table-hover">
                <thead>
                <tr>
                    <th><?= Yii::t('JobsModule.base', 'Titel') ?></th>
                    <th><?= Yii::t('JobsModule.base', 'Status') ?></th>
                    <th><?= Yii::t('JobsModule.base', 'Online bis') ?></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($listings as $l): ?>
                    <tr>
                        <td><?= Html::a(Html::encode($l->title), ['/jobs/job/view', 'id' => $l->id]) ?></td>
                        <td><span class="label label-default"><?= Html::encode(Yii::t('JobsModule.base', 'status.' . $l->status)) ?></span></td>
                        <td><?= $l->published_until ? Html::encode(substr($l->published_until, 0, 10)) : '–' ?></td>
                        <td class="text-right">
                            <?php if ($l->status === JobListing::STATUS_DRAFT): ?>
                                <?= Html::a(Yii::t('JobsModule.base', 'Bezahlen & veröffentlichen'), ['/jobs/checkout/select', 'id' => $l->id], ['class' => 'btn btn-success btn-xs']) ?>
                            <?php endif; ?>
                            <?= Html::a(Yii::t('JobsModule.base', 'Bearbeiten'), ['/jobs/job/edit', 'id' => $l->id], ['class' => 'btn btn-default btn-xs']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
