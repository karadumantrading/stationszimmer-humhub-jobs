<?php

namespace humhub\modules\jobs;

use Yii;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\jobs\models\JobListing;

/**
 * Event-Handler: Top-Menü-Eintrag und täglicher Ablauf-Cron.
 *
 * Menü-Muster 1:1 wie HumHub-Core (modules/dashboard/Events.php): MenuLink mit
 * Config-Array, Icon als String (kein Icon-Widget), isActiveState().
 */
class Events
{
    /** «Jobbörse» ins Top-Menü hängen. */
    public static function onTopMenuInit($event): void
    {
        $event->sender->addEntry(new MenuLink([
            'id' => 'jobs',
            'label' => Yii::t('JobsModule.base', 'Jobbörse'),
            'url' => ['/jobs/job/index'],
            'icon' => 'briefcase',
            'sortOrder' => 300,
            'isActive' => MenuLink::isActiveState('jobs'),
        ]));
    }

    /** Täglich: veröffentlichte Inserate nach Ablauf auf 'expired' setzen. */
    public static function onDailyCron($event): void
    {
        JobListing::updateAll(
            ['status' => JobListing::STATUS_EXPIRED, 'updated_at' => date('Y-m-d H:i:s')],
            [
                'and',
                ['status' => JobListing::STATUS_PUBLISHED],
                ['<', 'published_until', date('Y-m-d H:i:s')],
            ]
        );
    }
}
