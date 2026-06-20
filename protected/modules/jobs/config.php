<?php

use humhub\modules\jobs\Events;
use humhub\modules\jobs\Module;

// @verify: Event-Klassen/Konstanten gegen die installierte HumHub-Version prüfen.
// - TopMenu:   humhub\widgets\TopMenu::EVENT_INIT
// - DailyCron: humhub\modules\cron\CronController::EVENT_ON_DAILY_RUN
return [
    'id' => 'jobs',
    'class' => Module::class,
    'namespace' => 'humhub\\modules\\jobs',
    'events' => [
        [
            'class' => \humhub\widgets\TopMenu::class,
            'event' => \humhub\widgets\TopMenu::EVENT_INIT,
            'callback' => [Events::class, 'onTopMenuInit'],
        ],
        [
            'class' => \humhub\modules\cron\CronController::class,
            'event' => \humhub\modules\cron\CronController::EVENT_ON_DAILY_RUN,
            'callback' => [Events::class, 'onDailyCron'],
        ],
    ],
    'consoleControllerMap' => [
        'jobs' => \humhub\modules\jobs\console\ExpireController::class,
    ],
];
