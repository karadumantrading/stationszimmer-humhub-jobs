<?php

namespace humhub\modules\forum;

use Yii;

/**
 * Event-Handler: «Forum» ins Top-Menü hängen.
 *
 * @verify: Menü-/Icon-Klassen gegen installierte HumHub-Version.
 */
class Events
{
    public static function onTopMenuInit($event): void
    {
        $entry = new \humhub\modules\ui\menu\MenuLink();
        $entry->setId('forum');
        $entry->setLabel(Yii::t('ForumModule.base', 'Forum'));
        $entry->setUrl(['/forum/category/index']);
        $entry->setIcon(new \humhub\modules\ui\widgets\Icon(['name' => 'comments']));
        $entry->setSortOrder(290);
        $entry->setIsActive(
            Yii::$app->controller->module
            && Yii::$app->controller->module->id === 'forum'
        );
        $event->sender->addEntry($entry);
    }
}
