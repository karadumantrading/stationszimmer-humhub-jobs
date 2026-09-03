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
    public $stripePriceSingle;   // CHF 300 Einzelinserat
    public $stripePriceFlat3m;   // CHF 1200 Flat 3 Monate
    public $stripePriceFlat12m;  // CHF 3000 Flat 12 Monate
    public $moderationEnabled;

    public function rules(): array
    {
        return [
            [['stripePriceSingle', 'stripePriceFlat3m', 'stripePriceFlat12m'], 'string', 'max' => 255],
            [['stripePriceSingle', 'stripePriceFlat3m', 'stripePriceFlat12m'], 'match',
                'pattern' => '/^price_[A-Za-z0-9]+$/', 'skipOnEmpty' => true,
                'message' => Yii::t('JobsModule.base', 'Bitte eine gültige Stripe-Price-ID (price_…) eingeben.')],
            [['moderationEnabled'], 'boolean'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'stripePriceSingle'  => Yii::t('JobsModule.base', 'Stripe-Price-ID Einzelinserat (CHF 300)'),
            'stripePriceFlat3m'  => Yii::t('JobsModule.base', 'Stripe-Price-ID Flat 3 Monate (CHF 1’200)'),
            'stripePriceFlat12m' => Yii::t('JobsModule.base', 'Stripe-Price-ID Flat 12 Monate (CHF 3’000)'),
            'moderationEnabled'  => Yii::t('JobsModule.base', 'Inserate vor Veröffentlichung prüfen'),
        ];
    }

    public function loadSettings(Module $module): void
    {
        $s = $module->settings;
        $this->stripePriceSingle  = $s->get('stripePriceSingle', '');
        $this->stripePriceFlat3m  = $s->get('stripePriceFlat3m', '');
        $this->stripePriceFlat12m = $s->get('stripePriceFlat12m', '');
        $this->moderationEnabled  = $module->isModerationEnabled();
    }

    public function saveSettings(Module $module): bool
    {
        if (!$this->validate()) {
            return false;
        }
        $s = $module->settings;
        $s->set('stripePriceSingle',  trim((string) $this->stripePriceSingle));
        $s->set('stripePriceFlat3m',  trim((string) $this->stripePriceFlat3m));
        $s->set('stripePriceFlat12m', trim((string) $this->stripePriceFlat12m));
        $s->set('moderationEnabled',  $this->moderationEnabled ? '1' : '0');
        return true;
    }
}
