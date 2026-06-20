<?php

namespace humhub\modules\jobs\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use humhub\modules\jobs\Module;
use humhub\modules\user\models\User;

/**
 * Stelleninserat.
 *
 * @property int $id
 * @property int $created_by
 * @property string $company_name
 * @property string $title
 * @property string $setting
 * @property string $canton
 * @property int|null $pensum_min
 * @property int|null $pensum_max
 * @property string $employment_type
 * @property string $description
 * @property string|null $contact_email
 * @property string|null $contact_url
 * @property string|null $location
 * @property string|null $tier
 * @property int $is_top
 * @property string $status
 * @property string|null $stripe_checkout_session_id
 * @property string|null $stripe_payment_intent_id
 * @property string|null $published_at
 * @property string|null $published_until
 * @property string $created_at
 * @property string $updated_at
 */
class JobListing extends ActiveRecord
{
    /** Szenario: nur vom Arbeitgeber befüllbare Felder (status/tier/stripe NICHT). */
    public const SCENARIO_EMPLOYER = 'employer';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING_PAYMENT = 'pending_payment';
    public const STATUS_PENDING_REVIEW = 'pending_review'; // bezahlt/gratis, wartet auf Moderation
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_ARCHIVED = 'archived';
    public const STATUS_REJECTED = 'rejected';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_PENDING_PAYMENT,
        self::STATUS_PENDING_REVIEW,
        self::STATUS_PUBLISHED,
        self::STATUS_EXPIRED,
        self::STATUS_ARCHIVED,
        self::STATUS_REJECTED,
    ];

    /** Whitelists (gegen die Stationszimmer-Settings abgleichen). */
    public const SETTINGS = ['spital', 'spitex', 'langzeit', 'psychiatrie', 'rehabilitation', 'ausbildung'];
    public const EMPLOYMENT_TYPES = ['festanstellung', 'temporaer', 'lehrstelle'];
    public const CANTONS = [
        'AG', 'AI', 'AR', 'BE', 'BL', 'BS', 'FR', 'GE', 'GL', 'GR',
        'JU', 'LU', 'NE', 'NW', 'OW', 'SG', 'SH', 'SO', 'SZ', 'TG',
        'TI', 'UR', 'VD', 'VS', 'ZG', 'ZH',
    ];

    public static function tableName(): string
    {
        return 'job_listing';
    }

    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['company_name', 'title', 'setting', 'canton', 'employment_type', 'description'], 'required'],
            [['company_name', 'title'], 'string', 'max' => 255],
            [['description'], 'string'],
            [['contact_email'], 'email'],
            [['contact_email'], 'string', 'max' => 190],
            [['contact_url'], 'url', 'defaultScheme' => 'https'],
            [['contact_url'], 'string', 'max' => 255],
            [['location'], 'string', 'max' => 190],
            [['pensum_min', 'pensum_max'], 'integer', 'min' => 0, 'max' => 100],
            [['pensum_max'], 'compare', 'compareAttribute' => 'pensum_min', 'operator' => '>=',
                'skipOnEmpty' => true, 'when' => fn($m) => $m->pensum_min !== null && $m->pensum_max !== null],
            // Whitelist-Validierung
            ['setting', 'in', 'range' => self::SETTINGS],
            ['canton', 'in', 'range' => self::CANTONS],
            ['employment_type', 'in', 'range' => self::EMPLOYMENT_TYPES],
            ['tier', 'in', 'range' => Module::TIERS, 'skipOnEmpty' => true],
            ['status', 'in', 'range' => self::STATUSES],
        ];
    }

    /**
     * Im Arbeitgeber-Szenario sind NUR die fachlichen Felder massenzuweisbar –
     * status/tier/stripe_*/published_* werden ausschliesslich serverseitig gesetzt.
     */
    public function scenarios(): array
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_EMPLOYER] = [
            'company_name', 'title', 'setting', 'canton', 'pensum_min', 'pensum_max',
            'employment_type', 'description', 'contact_email', 'contact_url', 'location',
        ];
        return $scenarios;
    }

    public function attributeLabels(): array
    {
        return [
            'company_name' => Yii::t('JobsModule.base', 'Arbeitgeber'),
            'title' => Yii::t('JobsModule.base', 'Titel'),
            'setting' => Yii::t('JobsModule.base', 'Bereich'),
            'canton' => Yii::t('JobsModule.base', 'Kanton'),
            'pensum_min' => Yii::t('JobsModule.base', 'Pensum von (%)'),
            'pensum_max' => Yii::t('JobsModule.base', 'Pensum bis (%)'),
            'employment_type' => Yii::t('JobsModule.base', 'Anstellungsart'),
            'description' => Yii::t('JobsModule.base', 'Beschreibung'),
            'contact_email' => Yii::t('JobsModule.base', 'Kontakt-E-Mail'),
            'contact_url' => Yii::t('JobsModule.base', 'Link zur Ausschreibung'),
            'location' => Yii::t('JobsModule.base', 'Ort'),
            'tier' => Yii::t('JobsModule.base', 'Tarif'),
            'status' => Yii::t('JobsModule.base', 'Status'),
        ];
    }

    public function getCreatedByUser()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /** Modul-Instanz (für Tarif-/Config-Zugriff). */
    public static function module(): Module
    {
        /** @var Module $m */
        $m = Yii::$app->getModule('jobs');
        return $m;
    }

    /** Laufzeit in Tagen für den gewählten Tier (serverseitig aus der Config). */
    public function tierDurationDays(): int
    {
        $def = $this->tier ? self::module()->getTier($this->tier) : null;
        return (int) ($def['durationDays'] ?? self::module()->getDurationDays());
    }

    /** Aktiv = veröffentlicht und nicht abgelaufen. */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_PUBLISHED
            && $this->published_until !== null
            && strtotime($this->published_until) >= time();
    }

    /**
     * Inserat veröffentlichen: Laufzeit serverseitig aus dem Tier, published_at/until
     * setzen. Wird vom Webhook (nach Zahlung) bzw. Admin (nach Freigabe) aufgerufen.
     */
    public function publish(): void
    {
        $days = $this->tierDurationDays();
        $now = date('Y-m-d H:i:s');
        $this->updateAttributes([
            'status' => self::STATUS_PUBLISHED,
            'is_top' => !empty(self::module()->getTier((string) $this->tier)['isTop']) ? 1 : 0,
            'published_at' => $now,
            'published_until' => date('Y-m-d H:i:s', strtotime("+{$days} days")),
            'updated_at' => $now,
        ]);
    }

    /** Bezahlt/gratis, aber Moderation aktiv: in Review-Status setzen. */
    public function markPendingReview(): void
    {
        $this->updateAttributes([
            'status' => self::STATUS_PENDING_REVIEW,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /** Darf der/die User bearbeiten/löschen? (Ersteller:in oder Admin) */
    public function canManage(?User $user = null): bool
    {
        $user = $user ?? Yii::$app->user->getIdentity();
        if ($user === null) {
            return false;
        }
        return (int) $this->created_by === (int) $user->id || $user->isSystemAdmin();
    }
}
