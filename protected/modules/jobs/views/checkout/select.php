<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $listing humhub\modules\jobs\models\JobListing */
/* @var $tiers array  Schlüssel => Tier-Definition (nur aktuell wählbare) */

$this->title = Yii::t('JobsModule.base', 'Tarif wählen');
?>
<div class="panel panel-default">
    <div class="panel-heading"><strong><?= Html::encode($this->title) ?></strong></div>
    <div class="panel-body">
        <p class="text-muted">
            <?= Yii::t('JobsModule.base', 'Inserat: {title}', ['title' => Html::encode($listing->title)]) ?>
        </p>

        <?php if (empty($tiers)): ?>
            <div class="alert alert-warning">
                <?= Yii::t('JobsModule.base', 'Aktuell sind keine Tarife verfügbar. Bitte später erneut versuchen.') ?>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($tiers as $key => $def): ?>
                    <div class="col-sm-6 col-md-3">
                        <div class="panel panel-<?= !empty($def['isTop']) ? 'warning' : 'default' ?>">
                            <div class="panel-heading text-center">
                                <strong><?= Html::encode($def['label']) ?></strong>
                            </div>
                            <div class="panel-body text-center">
                                <p>
                                    <?php if (!empty($def['free'])): ?>
                                        <span class="lead"><?= Yii::t('JobsModule.base', 'Gratis') ?></span>
                                    <?php else: ?>
                                        <span class="text-muted"><?= Yii::t('JobsModule.base', 'gemäss Stripe') ?></span>
                                    <?php endif; ?>
                                </p>
                                <p class="text-muted">
                                    <?= Yii::t('JobsModule.base', '{days} Tage online', ['days' => (int) $def['durationDays']]) ?>
                                </p>
                                <?= Html::beginForm(['/jobs/checkout/checkout', 'id' => $listing->id], 'post') ?>
                                <?= Html::hiddenInput('tier', $key) ?>
                                <?= Html::submitButton(
                                    !empty($def['free']) ? Yii::t('JobsModule.base', 'Gratis veröffentlichen') : Yii::t('JobsModule.base', 'Auswählen & bezahlen'),
                                    ['class' => 'btn btn-' . (!empty($def['isTop']) ? 'warning' : 'primary') . ' btn-block']
                                ) ?>
                                <?= Html::endForm() ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <p class="text-muted small">
                <?= Yii::t('JobsModule.base', 'Die Bezahlung läuft sicher über Stripe (gehostete Seite). Kartendaten erreichen unseren Server nicht.') ?>
            </p>
        <?php endif; ?>
    </div>
</div>
