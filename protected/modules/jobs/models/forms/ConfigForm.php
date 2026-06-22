<?php

namespace humhub\modules\jobs\models\forms;

use Yii;
use yii\base\Model;
use humhub\modules\jobs\Module;

/**
 * Modul-Konfiguration (Admin). Speichert in die HumHub-Modul-Settings.
 *
 * WICHTIG: Hier liegen NUR Price-IDs/Limit/Flags – NIEMALS der Stripe Secret Key
 * oder das Webhook-Secret (die gehören in Yii::$app->params/Env).
 */
class ConfigForm extends Model
{
    public $stripePriceIntro;
    public $stripePriceBasis;
    public $stripePriceTop;
    public $introListingLimit;
    public $moderationEnabled;
    public $durationDays;

    public function rules(): array
    {
        return [
            [['stripePriceIntro', 'stripePriceBasis', 'stripePriceTop'], 'string', 'max' => 255],
            [['stripePriceIntro', 'stripePriceBasis', 'stripePriceTop'], 'match',
                'pattern' => '/^price_[A-Za-z0-9]+$/', 'skipOnEmpty' => true,
                'message' => Yii::t('JobsModule.base', 'Bitte eine gültige Stripe-Price-ID (price_…) eingeben.')],
            [['introListingLimit'], 'integer', 'min' => 0, 'max' => 100000],
            [['moderationEnabled'], 'boolean'],
            [['durationDays'], 'integer', 'min' => 1, 'max' => 365],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'stripePriceIntro' => Yii::t('JobsModule.base', 'Stripe-Price-ID «Intro» (CHF 49)'),
            'stripePriceBasis' => Yii::t('JobsModule.base', 'Stripe-Price-ID «Basis» (CHF 99)'),
            'stripePriceTop' => Yii::t('JobsModule.base', 'Stripe-Price-ID «Top» (CHF 199)'),
            'introListingLimit' => Yii::t('JobsModule.base', 'Intro-Limit (Anzahl bezahlter Inserate)'),
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
        $this->introListingLimit = $module->getIntroListingLimit();
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
        $s->set('introListingLimit', (int) $this->introListingLimit);
        $s->set('moderationEnabled', $this->moderationEnabled ? '1' : '0');
        $s->set('durationDays', (int) $this->durationDays);
        return true;
    }
}
