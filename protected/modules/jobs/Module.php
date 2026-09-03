<?php

namespace humhub\modules\jobs;

use Yii;
use yii\helpers\Url;
use humhub\modules\jobs\models\JobListing;

/**
 * Modul «Jobbörse».
 *
 * Hält die serverseitige Tarif-Hoheit: Tier-Definitionen (Preis-IDs, Dauer,
 * Intro-Limit, Moderation) kommen ausschliesslich aus den Modul-Settings, NIE
 * aus dem Client. Stripe-Secrets liegen NICHT hier, sondern in Yii::$app->params.
 *
 * @verify: humhub\components\Module gegen installierte Version.
 */
class Module extends \humhub\components\Module
{
    /** Tier-Schlüssel (Whitelist). */
    public const TIER_SINGLE      = 'single';       // CHF 300, unbegrenzt, 1 Inserat
    public const TIER_FLAT_3M     = 'flat_3m';      // CHF 1200, 3 Monate, unbegrenzt viele
    public const TIER_FLAT_12M    = 'flat_12m';     // CHF 3000, 12 Monate, unbegrenzt viele
    public const TIER_LEHRSTELLE  = 'lehrstelle';   // gratis

    public const TIERS = [
        self::TIER_SINGLE,
        self::TIER_FLAT_3M,
        self::TIER_FLAT_12M,
        self::TIER_LEHRSTELLE,
    ];

    /** Ab wie vielen bezahlten Einzelinseraten werden Flat-Abos freigeschaltet? */
    public const FLAT_UNLOCK_THRESHOLD = 5;

    /** Konfigurationsseite im Admin. */
    public function getConfigUrl()
    {
        return Url::to(['/jobs/admin/config']);
    }

    /** Ist Moderation (Freigabe vor Veröffentlichung) aktiv? */
    public function isModerationEnabled(): bool
    {
        return (bool) $this->settings->get('moderationEnabled', true);
    }

    /**
     * Anzahl bezahlter Einzelinserate (tier=single, Zahlung abgeschlossen) des Users.
     * Basis für Flat-Freischaltung.
     */
    public function getPaidSingleCount(int $userId): int
    {
        return (int) JobListing::find()
            ->andWhere(['created_by' => $userId])
            ->andWhere(['tier' => self::TIER_SINGLE])
            ->andWhere(['not', ['stripe_payment_intent_id' => null]])
            ->count();
    }

    /** Hat dieser User das Flat-Abo freigeschaltet? */
    public function isFlatUnlocked(int $userId): bool
    {
        return $this->getPaidSingleCount($userId) >= self::FLAT_UNLOCK_THRESHOLD;
    }

    /**
     * Vollständige Tier-Definitionen.
     * durationDays = null bedeutet «unbegrenzt / läuft bis Deaktivierung».
     */
    public function getTiers(): array
    {
        return [
            self::TIER_SINGLE => [
                'label'          => Yii::t('JobsModule.base', 'Einzelinserat (CHF 300)'),
                'durationDays'   => null,   // unbegrenzt
                'stripePriceId'  => trim((string) $this->settings->get('stripePriceSingle', '')),
                'flat'           => false,
                'free'           => false,
            ],
            self::TIER_FLAT_3M => [
                'label'          => Yii::t('JobsModule.base', 'Flat 3 Monate (CHF 1’200)'),
                'durationDays'   => 90,
                'stripePriceId'  => trim((string) $this->settings->get('stripePriceFlat3m', '')),
                'flat'           => true,
                'free'           => false,
            ],
            self::TIER_FLAT_12M => [
                'label'          => Yii::t('JobsModule.base', 'Flat 12 Monate (CHF 3’000)'),
                'durationDays'   => 365,
                'stripePriceId'  => trim((string) $this->settings->get('stripePriceFlat12m', '')),
                'flat'           => true,
                'free'           => false,
            ],
            self::TIER_LEHRSTELLE => [
                'label'          => Yii::t('JobsModule.base', 'Lehrstelle (gratis)'),
                'durationDays'   => null,
                'stripePriceId'  => null,
                'flat'           => false,
                'free'           => true,
            ],
        ];
    }

    /** Einzelne Tier-Definition oder null. */
    public function getTier(string $tier): ?array
    {
        return $this->getTiers()[$tier] ?? null;
    }

    /**
     * Aktuell wählbare Tiers für diesen User:
     * - Lehrstelle immer (gratis)
     * - Einzelinserat immer (mit Price-ID)
     * - Flat-Abos nur wenn FLAT_UNLOCK_THRESHOLD bezahlte Einzelinserate erreicht
     */
    public function getAvailableTiers(?int $userId = null): array
    {
        $userId = $userId ?? (int) Yii::$app->user->id;
        $flatUnlocked = $this->isFlatUnlocked($userId);
        $out = [];
        foreach ($this->getTiers() as $key => $def) {
            if (!empty($def['free'])) {
                $out[$key] = $def;
                continue;
            }
            if (empty($def['stripePriceId'])) {
                continue;
            }
            if (!empty($def['flat']) && !$flatUnlocked) {
                continue; // Flat noch nicht freigeschaltet
            }
            $out[$key] = $def;
        }
        return $out;
    }

    /** Ist dieser Tier für diesen User verfügbar? */
    public function isTierAvailable(string $tier, ?int $userId = null): bool
    {
        return array_key_exists($tier, $this->getAvailableTiers($userId));
    }
}
