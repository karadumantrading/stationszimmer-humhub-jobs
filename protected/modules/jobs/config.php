<?php

use humhub\modules\jobs\Events;
use humhub\modules\jobs\Module;

// Verifiziert gegen HumHub-Source im Container:
// - TopMenu:   humhub\widgets\TopMenu::EVENT_INIT  ✓
// - DailyCron: humhub\commands\CronController::EVENT_ON_DAILY_RUN ("daily")  ✓
//   (liegt in protected/humhub/commands/, NICHT unter modules/cron/)
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
            'class' => \humhub\commands\CronController::class,
            'event' => \humhub\commands\CronController::EVENT_ON_DAILY_RUN,
            'callback' => [Events::class, 'onDailyCron'],
        ],
    ],
    'consoleControllerMap' => [
        'jobs' => \humhub\modules\jobs\console\ExpireController::class,
    ],
];
