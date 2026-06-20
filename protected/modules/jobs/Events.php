<?php

namespace humhub\modules\jobs;

use Yii;
use humhub\modules\jobs\models\JobListing;

/**
 * Event-Handler des Moduls: Top-Menü-Eintrag und täglicher Ablauf-Cron.
 *
 * @verify: Menü-/Icon-Klassen gegen installierte HumHub-Version
 * (humhub\modules\ui\menu\MenuLink, humhub\modules\ui\widgets\Icon).
 */
class Events
{
    /** «Jobbörse» ins Top-Menü hängen. */
    public static function onTopMenuInit($event): void
    {
        $entry = new \humhub\modules\ui\menu\MenuLink();
        $entry->setId('jobs');
        $entry->setLabel(Yii::t('JobsModule.base', 'Jobbörse'));
        $entry->setUrl(['/jobs/job/index']);
        $entry->setIcon(new \humhub\modules\ui\widgets\Icon(['name' => 'briefcase']));
        $entry->setSortOrder(300);
        $entry->setIsActive(
            Yii::$app->controller->module
            && Yii::$app->controller->module->id === 'jobs'
        );
        $event->sender->addEntry($entry);
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
