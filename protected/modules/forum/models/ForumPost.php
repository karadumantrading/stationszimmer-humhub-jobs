<?php

namespace humhub\modules\forum\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use humhub\modules\forum\Module;
use humhub\modules\user\models\User;

/**
 * Forum-Beitrag (Eröffnung oder Antwort).
 *
 * @property int $id
 * @property int $thread_id
 * @property int $created_by
 * @property string $message
 * @property string $created_at
 * @property string $updated_at
 */
class ForumPost extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'forum_post';
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
            [['message'], 'required'],
            [['message'], 'string'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'message' => Yii::t('ForumModule.base', 'Beitrag'),
        ];
    }

    public function getThread()
    {
        return $this->hasOne(ForumThread::class, ['id' => 'thread_id']);
    }

    public function getAuthor()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /** Ist dies der Eröffnungsbeitrag des Themas? */
    public function isOpeningPost(): bool
    {
        $firstId = (int) ForumPost::find()->where(['thread_id' => $this->thread_id])
            ->min('id');
        return $firstId === (int) $this->id;
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
