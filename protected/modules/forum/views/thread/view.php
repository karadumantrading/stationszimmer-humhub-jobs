<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ListView;
use yii\widgets\ActiveForm;
use humhub\modules\forum\Module;

/* @var $this yii\web\View */
/* @var $thread humhub\modules\forum\models\ForumThread */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $reply humhub\modules\forum\models\ForumPost */

$this->title = $thread->title;
$canMod = Module::canModerate();
$canManageThread = $thread->canManage();
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <?php if ($thread->category): ?>
            <a href="<?= Url::to(['/forum/category/view', 'id' => $thread->category_id]) ?>" class="text-muted">&laquo; <?= Html::encode($thread->category->title) ?></a>
        <?php endif; ?>
        <div class="pull-right">
            <?php if ($canMod): ?>
                <?= Html::a($thread->is_pinned ? Yii::t('ForumModule.base', 'Lösen') : Yii::t('ForumModule.base', 'Anpinnen'),
                    ['/forum/thread/pin', 'id' => $thread->id], ['class' => 'btn btn-default btn-xs', 'data-method' => 'post']) ?>
                <?= Html::a($thread->is_locked ? Yii::t('ForumModule.base', 'Öffnen') : Yii::t('ForumModule.base', 'Schliessen'),
                    ['/forum/thread/lock', 'id' => $thread->id], ['class' => 'btn btn-default btn-xs', 'data-method' => 'post']) ?>
            <?php endif; ?>
            <?php if ($canManageThread): ?>
                <?= Html::a('<i class="fa fa-trash"></i>', ['/forum/thread/delete', 'id' => $thread->id],
                    ['class' => 'btn btn-danger btn-xs', 'data-method' => 'post',
                     'data-confirm' => Yii::t('ForumModule.base', 'Thema wirklich löschen?')]) ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="panel-body">
        <h3 style="margin-top:0">
            <?php if ($thread->is_pinned): ?><i class="fa fa-thumb-tack text-warning"></i> <?php endif; ?>
            <?php if ($thread->is_locked): ?><i class="fa fa-lock text-muted"></i> <?php endif; ?>
            <?= Html::encode($thread->title) ?>
        </h3>

        <?= ListView::widget([
            'dataProvider' => $dataProvider,
            'itemView' => '_post',
            'layout' => "{items}\n{pager}",
            'itemOptions' => ['tag' => false],
        ]) ?>
    </div>
</div>

<?php if (!Yii::$app->user->isGuest && (!$thread->is_locked || $canMod)): ?>
    <div class="panel panel-default">
        <div class="panel-body">
            <?php $form = ActiveForm::begin(['action' => ['/forum/thread/reply', 'id' => $thread->id]]); ?>
            <?= $form->field($reply, 'message')->textarea(['rows' => 5, 'placeholder' => Yii::t('ForumModule.base', 'Antwort schreiben …')])->label(false) ?>
            <?= Html::submitButton(Yii::t('ForumModule.base', 'Antworten'), ['class' => 'btn btn-primary']) ?>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
<?php elseif ($thread->is_locked): ?>
    <p class="text-muted"><i class="fa fa-lock"></i> <?= Yii::t('ForumModule.base', 'Dieses Thema ist geschlossen.') ?></p>
<?php elseif (Yii::$app->user->isGuest): ?>
    <p class="text-muted"><?= Yii::t('ForumModule.base', 'Melde dich an, um mitzudiskutieren.') ?></p>
<?php endif; ?>
