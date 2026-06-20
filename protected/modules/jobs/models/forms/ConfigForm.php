<?php

namespace humhub\modules\jobs\models\forms;

use Yii;
use yii\base\Model;
use humhub\modules\jobs\Module;

/**
 * Modul-Konfiguration (Admin). Speichert in die HumHub-Modul-Settings.
 *
 * WICHTIG: Hier liegen NUR Price-IDs/Stichdatum/Flags – NIEMALS der Stripe
 * Secret Key oder das Webhook-Secret (die gehören in Yii::$app->params/Env).
 */
class ConfigForm extends Model
{
    public $stripePriceIntro;
    public $stripePriceBasis;
    public $stripePriceTop;
    public $introValidUntil;
    public $moderationEnabled;
    public $durationDays;

    public function rules(): array
    {
        return [
            [['stripePriceIntro', 'stripePriceBasis', 'stripePriceTop'], 'string', 'max' => 255],
            [['stripePriceIntro', 'stripePriceBasis', 'stripePriceTop'], 'match',
                'pattern' => '/^price_[A-Za-z0-9]+$/', 'skipOnEmpty' => true,
                'message' => Yii::t('JobsModule.base', 'Bitte eine gültige Stripe-Price-ID (price_…) eingeben.')],
            [['introValidUntil'], 'date', 'format' => 'php:Y-m-d', 'skipOnEmpty' => true],
            [['moderationEnabled'], 'boolean'],
            [['durationDays'], 'integer', 'min' => 1, 'max' => 365],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'stripePriceIntro' => Yii::t('JobsModule.base', 'Stripe-Price-ID «Intro» (CHF 49)'),
            'stripePriceBasis' => Yii::t('JobsModule.base', 'Stripe-Price-ID «Basis»'),
            'stripePriceTop' => Yii::t('JobsModule.base', 'Stripe-Price-ID «Top»'),
            'introValidUntil' => Yii::t('JobsModule.base', 'Intro-Stichdatum (letzter Tag)'),
            'moderationEnabled' => Yii::t('JobsModule.base', 'Inserate vor Veröffentlichung prüfen'),
            'durationDays' => Yii::t('JobsModule.base', 'Laufzeit pro Inserat (Tage)'),
        ];
    }

    public function loadSettings(Module $module): void
    {
        $s = $module->settings;
        $this->stripePriceIntro = $s->get('stripePriceIntro', '');
        $this->stripePriceBasis = $s->get('stripePriceBasis', '');
        $this->stripePriceTop = $s->get('stripePriceTop', '');
        $this->introValidUntil = $module->getIntroValidUntil()
            ? substr((string) $module->getIntroValidUntil(), 0, 10)
            : '';
        $this->moderationEnabled = $module->isModerationEnabled();
        $this->durationDays = $module->getDurationDays();
    }

    public function saveSettings(Module $module): bool
    {
        if (!$this->validate()) {
            return false;
        }
        $s = $module->settings;
        $s->set('stripePriceIntro', trim((string) $this->stripePriceIntro));
        $s->set('stripePriceBasis', trim((string) $this->stripePriceBasis));
        $s->set('stripePriceTop', trim((string) $this->stripePriceTop));
        // Stichdatum inkl. Tagesende speichern.
        $s->set('introValidUntil', $this->introValidUntil ? $this->introValidUntil . ' 23:59:59' : '');
        $s->set('moderationEnabled', $this->moderationEnabled ? '1' : '0');
        $s->set('durationDays', (int) $this->durationDays);
        return true;
    }
}
