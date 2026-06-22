<?php

namespace humhub\modules\jobs\console;

use yii\console\Controller;
use yii\console\ExitCode;
use humhub\modules\jobs\models\JobListing;

/**
 * Konsolen-Fallback zum Ablaufenlassen: `php protected/yii jobs/expire`.
 * Im Normalbetrieb erledigt das der Daily-Cron (Events::onDailyCron).
 */
class ExpireController extends Controller
{
    public function actionExpire(): int
    {
        $count = JobListing::updateAll(
            ['status' => JobListing::STATUS_EXPIRED, 'updated_at' => date('Y-m-d H:i:s')],
            [
                'and',
                ['status' => JobListing::STATUS_PUBLISHED],
                ['<', 'published_until', date('Y-m-d H:i:s')],
            ]
        );

        $this->stdout("Abgelaufene Inserate gesetzt: {$count}\n");
        return ExitCode::OK;
    }
}
