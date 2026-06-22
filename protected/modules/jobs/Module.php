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

    /** Standard-Limit für die Intro-Aktion (Anzahl bezahlter Inserate). */
    public const DEFAULT_INTRO_LIMIT = 50;

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

    /** Ist Moderation (Freigabe vor Veröffentlichung) aktiv? (Gründungsphase: AN) */
    public function isModerationEnabled(): bool
    {
        return (bool) $this->settings->get('moderationEnabled', true);
    }

    /** Bis zu wie vielen bezahlten Inseraten gilt der Intro-Tarif? */
    public function getIntroListingLimit(): int
    {
        $v = $this->settings->get('introListingLimit', self::DEFAULT_INTRO_LIMIT);
        return ($v === null || $v === '') ? self::DEFAULT_INTRO_LIMIT : (int) $v;
    }

    /**
     * Anzahl bereits bezahlter Inserate (Tier ≠ Lehrstelle, Zahlung abgeschlossen).
     * Wird direkt aus der Tabelle abgeleitet – kein Datum, keine Extra-Spalte.
     */
    public function getPaidListingCount(): int
    {
        return (int) JobListing::find()
            ->andWhere(['not', ['tier' => self::TIER_LEHRSTELLE]])
            ->andWhere(['not', ['paid_at' => null]])
            ->count();
    }

    /** Ist die Intro-Aktion noch verfügbar (Kontingent nicht ausgeschöpft)? */
    public function isIntroAvailable(): bool
    {
        return $this->getPaidListingCount() < $this->getIntroListingLimit();
    }

    /**
     * Vollständige Tier-Definitionen (label, durationDays, stripePriceId, isTop,
     * free). Reine Konfiguration – Verfügbarkeit/Erlaubnis siehe getAvailableTiers().
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
            ],
            self::TIER_BASIS => [
                'label' => Yii::t('JobsModule.base', 'Basis'),
                'durationDays' => $days,
                'stripePriceId' => trim((string) $this->settings->get('stripePriceBasis', '')),
                'isTop' => false,
                'free' => false,
            ],
            self::TIER_TOP => [
                'label' => Yii::t('JobsModule.base', 'Top'),
                'durationDays' => $days,
                'stripePriceId' => trim((string) $this->settings->get('stripePriceTop', '')),
                'isTop' => true,
                'free' => false,
            ],
            self::TIER_LEHRSTELLE => [
                'label' => Yii::t('JobsModule.base', 'Lehrstelle'),
                'durationDays' => $days,
                'stripePriceId' => null,
                'isTop' => false,
                'free' => true,
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
     * - Lehrstelle immer (gratis),
     * - bezahlte Tiers nur mit hinterlegter Stripe-Price-ID,
     * - Intro zusätzlich nur, solange das Kontingent (erste N bezahlten Inserate)
     *   nicht ausgeschöpft ist.
     */
    public function getAvailableTiers(): array
    {
        $out = [];
        foreach ($this->getTiers() as $key => $def) {
            if (!empty($def['free'])) {
                $out[$key] = $def;
                continue;
            }
            if (empty($def['stripePriceId'])) {
                continue; // ohne Price-ID nicht anbietbar
            }
            if ($key === self::TIER_INTRO && !$this->isIntroAvailable()) {
                continue; // Intro-Kontingent ausgeschöpft
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
