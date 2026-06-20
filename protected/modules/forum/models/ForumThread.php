<?php

namespace humhub\modules\forum\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use humhub\modules\forum\Module;
use humhub\modules\user\models\User;

/**
 * Forum-Thema. Der Eröffnungsbeitrag liegt als erster ForumPost vor.
 *
 * @property int $id
 * @property int $category_id
 * @property string $title
 * @property int $created_by
 * @property string $lang
 * @property int $is_pinned
 * @property int $is_locked
 * @property string|null $last_post_at
 * @property string $created_at
 * @property string $updated_at
 */
class ForumThread extends ActiveRecord
{
    public const SCENARIO_CREATE = 'create';
    public const LANGS = ['de', 'fr'];

    public static function tableName(): string
    {
        return 'forum_thread';
    }

    public function behaviors(): array
    {
        return [[
            'class' => TimestampBehavior::class,
            'value' => new Expression('NOW()'),
        ]];
    }

    public function rules(): array
    {
        return [
            [['title', 'category_id'], 'required'],
            [['title'], 'string', 'max' => 255],
            [['category_id'], 'exist', 'targetClass' => ForumCategory::class, 'targetAttribute' => 'id'],
            [['lang'], 'in', 'range' => self::LANGS],
            [['is_pinned', 'is_locked'], 'boolean'],
        ];
    }

    public function scenarios(): array
    {
        $s = parent::scenarios();
        // Beim Erstellen darf nur Titel (+ Sprache/Kategorie serverseitig) gesetzt werden.
        $s[self::SCENARIO_CREATE] = ['title'];
        return $s;
    }

    public function attributeLabels(): array
    {
        return [
            'title' => Yii::t('ForumModule.base', 'Titel'),
        ];
    }

    public function getCategory()
    {
        return $this->hasOne(ForumCategory::class, ['id' => 'category_id']);
    }

    public function getPosts()
    {
        return $this->hasMany(ForumPost::class, ['thread_id' => 'id'])->orderBy(['created_at' => SORT_ASC]);
    }

    public function getAuthor()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    public function getPostCount(): int
    {
        return (int) ForumPost::find()->where(['thread_id' => $this->id])->count();
    }

    /** Antworten = Beiträge ohne den Eröffnungsbeitrag. */
    public function getReplyCount(): int
    {
        return max(0, $this->getPostCount() - 1);
    }

    /** last_post_at aktualisieren (nach neuem Beitrag). */
    public function touchLastPost(): void
    {
        $this->updateAttributes(['last_post_at' => date('Y-m-d H:i:s')]);
    }

    public function canManage(?User $user = null): bool
    {
        $user = $user ?? Yii::$app->user->getIdentity();
        if ($user === null) {
            return false;
        }
        return (int) $this->created_by === (int) $user->id || Module::canModerate($user);
    }
}
