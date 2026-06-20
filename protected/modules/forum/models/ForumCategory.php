<?php

namespace humhub\modules\forum\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * Forum-Kategorie (Bereich).
 *
 * @property int $id
 * @property string $slug
 * @property string $title
 * @property string|null $description
 * @property string|null $icon
 * @property int $sort_order
 */
class ForumCategory extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'forum_category';
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
            [['slug', 'title'], 'required'],
            [['slug'], 'match', 'pattern' => '/^[a-z0-9\-]+$/'],
            [['slug'], 'unique'],
            [['slug'], 'string', 'max' => 60],
            [['title'], 'string', 'max' => 160],
            [['description'], 'string', 'max' => 400],
            [['icon'], 'string', 'max' => 40],
            [['sort_order'], 'integer'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'slug' => Yii::t('ForumModule.base', 'Kürzel (slug)'),
            'title' => Yii::t('ForumModule.base', 'Titel'),
            'description' => Yii::t('ForumModule.base', 'Beschreibung'),
            'icon' => Yii::t('ForumModule.base', 'Icon (FontAwesome)'),
            'sort_order' => Yii::t('ForumModule.base', 'Sortierung'),
        ];
    }

    public function getThreads()
    {
        return $this->hasMany(ForumThread::class, ['category_id' => 'id']);
    }

    public function getThreadCount(): int
    {
        return (int) ForumThread::find()->where(['category_id' => $this->id])->count();
    }
}
