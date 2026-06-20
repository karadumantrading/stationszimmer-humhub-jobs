<?php

namespace humhub\modules\jobs;

use Yii;
use yii\helpers\Url;

/**
 * Modul «Jobbörse».
 *
 * Hält die serverseitige Tarif-Hoheit: Tier-Definitionen (Preis-IDs, Dauer,
 * Intro-Stichdatum, Moderation) kommen ausschliesslich aus den Modul-Settings,
 * NIE aus dem Client. Stripe-Secrets liegen NICHT hier, sondern in Yii::$app->params.
 *
 * @verify: humhub\components\Module gegen installierte Version.
 */
class Module extends \humhub\components\Module
{
    /** Tier-Schlüssel (Whitelist). */
    public const TIER_INTRO = 'intro';
    public const TIER_BASIS = 'basis';
    public const TIER_TOP = 'top';
    public const TIER_LEHRSTELLE = 'lehrstelle';

    public const TIERS = [
        self::TIER_INTRO,
        self::TIER_BASIS,
        self::TIER_TOP,
        self::TIER_LEHRSTELLE,
    ];

    /** Konfigurationsseite im Admin. */
    public function getConfigUrl()
    {
        return Url::to(['/jobs/admin/config']);
    }

    /** Standard-Laufzeit eines Inserats in Tagen (global konfigurierbar). */
    public function getDurationDays(): int
    {
        return (int) ($this->settings->get('durationDays', 30)) ?: 30;
    }

    /** Ist Moderation (Freigabe vor Veröffentlichung) aktiv? */
    public function isModerationEnabled(): bool
    {
        return (bool) $this->settings->get('moderationEnabled', false);
    }

    /** Intro-Stichdatum (Y-m-d H:i:s) oder null. */
    public function getIntroValidUntil(): ?string
    {
        $v = trim((string) $this->settings->get('introValidUntil', ''));
        return $v !== '' ? $v : null;
    }

    /**
     * Vollständige Tier-Definitionen (label, durationDays, stripePriceId, isTop,
     * free, validUntil). Reine Konfiguration – keine Verfügbarkeitslogik.
     */
    public function getTiers(): array
    {
        $days = $this->getDurationDays();
        return [
            self::TIER_INTRO => [
                'label' => Yii::t('JobsModule.base', 'Intro'),
                'durationDays' => $days,
                'stripePriceId' => trim((string) $this->settings->get('stripePriceIntro', '')),
                'isTop' => false,
                'free' => false,
                'validUntil' => $this->getIntroValidUntil(),
            ],
            self::TIER_BASIS => [
                'label' => Yii::t('JobsModule.base', 'Basis'),
                'durationDays' => $days,
                'stripePriceId' => trim((string) $this->settings->get('stripePriceBasis', '')),
                'isTop' => false,
                'free' => false,
                'validUntil' => null,
            ],
            self::TIER_TOP => [
                'label' => Yii::t('JobsModule.base', 'Top'),
                'durationDays' => $days,
                'stripePriceId' => trim((string) $this->settings->get('stripePriceTop', '')),
                'isTop' => true,
                'free' => false,
                'validUntil' => null,
            ],
            self::TIER_LEHRSTELLE => [
                'label' => Yii::t('JobsModule.base', 'Lehrstelle'),
                'durationDays' => $days,
                'stripePriceId' => null,
                'isTop' => false,
                'free' => true,
                'validUntil' => null,
            ],
        ];
    }

    /** Einzelne Tier-Definition oder null. */
    public function getTier(string $tier): ?array
    {
        return $this->getTiers()[$tier] ?? null;
    }

    /**
     * Aktuell wählbare Tiers (serverseitige Erlaubnis):
     * - Intro nur bis zum Stichdatum,
     * - bezahlte Tiers nur mit hinterlegter Stripe-Price-ID,
     * - Lehrstelle immer.
     */
    public function getAvailableTiers(): array
    {
        $now = time();
        $out = [];
        foreach ($this->getTiers() as $key => $def) {
            if ($def['free']) {
                $out[$key] = $def;
                continue;
            }
            if (empty($def['stripePriceId'])) {
                continue; // ohne Price-ID nicht anbietbar
            }
            if (!empty($def['validUntil']) && strtotime($def['validUntil']) < $now) {
                continue; // Intro-Stichdatum überschritten
            }
            $out[$key] = $def;
        }
        return $out;
    }

    /** Ist dieser Tier jetzt wählbar? (serverseitige Validierung im Checkout) */
    public function isTierAvailable(string $tier): bool
    {
        return array_key_exists($tier, $this->getAvailableTiers());
    }
}
