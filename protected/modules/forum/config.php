<?php

use humhub\modules\forum\Events;
use humhub\modules\forum\Module;

// @verify: TopMenu-Klasse/Konstante gegen die installierte HumHub-Version.
return [
    'id' => 'forum',
    'class' => Module::class,
    'namespace' => 'humhub\\modules\\forum',
    'events' => [
        [
            'class' => \humhub\widgets\TopMenu::class,
            'event' => \humhub\widgets\TopMenu::EVENT_INIT,
            'callback' => [Events::class, 'onTopMenuInit'],
        ],
    ],
];
