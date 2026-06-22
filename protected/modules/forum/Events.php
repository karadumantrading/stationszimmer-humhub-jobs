<?php

namespace humhub\modules\forum;

use Yii;
use humhub\modules\ui\menu\MenuLink;

/**
 * Event-Handler: «Forum» ins Top-Menü hängen.
 *
 * Menü-Muster 1:1 wie HumHub-Core: MenuLink mit Config-Array, Icon als String.
 */
class Events
{
    public static function onTopMenuInit($event): void
    {
        $event->sender->addEntry(new MenuLink([
            'id' => 'forum',
            'label' => Yii::t('ForumModule.base', 'Forum'),
            'url' => ['/forum/category/index'],
            'icon' => 'comments',
            'sortOrder' => 290,
            'isActive' => MenuLink::isActiveState('forum'),
        ]));
    }
}
