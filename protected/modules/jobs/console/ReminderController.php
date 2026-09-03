<?php

namespace humhub\modules\jobs\console;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use humhub\modules\jobs\models\JobListing;

/**
 * 90-Tage-Reminder und Auto-Pause für Stelleninserate.
 *
 * Cronjob (täglich):
 *   php protected/yii jobs/reminder/run
 */
class ReminderController extends Controller
{
    /**
     * Sendet Bestätigungsmails an Inserate die seit 90 Tagen online sind
     * und pausiert Inserate die nach 7 Tagen nicht bestätigt wurden.
     */
    public function actionRun(): int
    {
        $this->autopause();
        $this->sendReminders();
        return ExitCode::OK;
    }

    /**
     * Pausiert Inserate die vor >7 Tagen eine Bestätigungsmail erhalten haben
     * aber nicht bestätigt wurden.
     */
    private function autopause(): void
    {
        $deadline = date('Y-m-d H:i:s', strtotime('-7 days'));

        $listings = JobListing::find()
            ->andWhere(['status' => JobListing::STATUS_PUBLISHED])
            ->andWhere(['not', ['confirmation_sent_at' => null]])
            ->andWhere(['paused_at' => null])
            ->andWhere(['<=', 'confirmation_sent_at', $deadline])
            ->all();

        foreach ($listings as $listing) {
            $listing->updateAttributes([
                'status'    => JobListing::STATUS_PAUSED,
                'paused_at' => date('Y-m-d H:i:s'),
            ]);

            // Benachrichtigungsmail
            $this->sendPausedMail($listing);

            Yii::info("Inserat #{$listing->id} «{$listing->title}» automatisch pausiert.", 'jobs.reminder');
        }
    }

    /**
     * Sendet Bestätigungsmails an Inserate die seit 90 Tagen online sind
     * und noch keine Erinnerung erhalten haben.
     */
    private function sendReminders(): void
    {
        $threshold = date('Y-m-d H:i:s', strtotime('-90 days'));

        $listings = JobListing::find()
            ->andWhere(['status' => JobListing::STATUS_PUBLISHED])
            ->andWhere(['confirmation_sent_at' => null])
            ->andWhere(['<=', 'published_at', $threshold])
            ->all();

        foreach ($listings as $listing) {
            $this->sendReminderMail($listing);

            $listing->updateAttributes([
                'confirmation_sent_at' => date('Y-m-d H:i:s'),
            ]);

            Yii::info("Bestätigungsmail für Inserat #{$listing->id} gesendet.", 'jobs.reminder');
        }
    }

    private function sendReminderMail(JobListing $listing): void
    {
        $user = $listing->createdByUser;
        if (!$user || !$user->email) {
            return;
        }

        // Bestätigungs-URL (1-Klick)
        $confirmUrl = \yii\helpers\Url::to(
            ['/jobs/job/confirm-active', 'id' => $listing->id, 'token' => $this->token($listing)],
            true
        );
        $deactivateUrl = \yii\helpers\Url::to(
            ['/jobs/job/deactivate', 'id' => $listing->id, 'token' => $this->token($listing)],
            true
        );

        Yii::$app->mailer->compose()
            ->setTo($user->email)
            ->setFrom([Yii::$app->settings->get('systemEmailAddress') => 'Stationszimmer'])
            ->setSubject('Ist diese Stelle noch offen? – ' . $listing->title)
            ->setHtmlBody(
                '<p>Guten Tag ' . htmlspecialchars($user->displayName) . '</p>' .
                '<p>Ihr Inserat <strong>' . htmlspecialchars($listing->title) . '</strong> ist seit 90 Tagen online.</p>' .
                '<p>Ist die Stelle noch offen?</p>' .
                '<p>' .
                '<a href="' . $confirmUrl . '" style="background:#120a8f;color:#fff;padding:10px 20px;text-decoration:none;border-radius:4px;margin-right:10px;">✓ Ja, Stelle ist noch offen</a>&nbsp;&nbsp;' .
                '<a href="' . $deactivateUrl . '" style="background:#64748b;color:#fff;padding:10px 20px;text-decoration:none;border-radius:4px;">Stelle besetzen / Inserat deaktivieren</a>' .
                '</p>' .
                '<p><small>Falls Sie nicht reagieren, wird das Inserat in 7 Tagen automatisch pausiert. Es bleibt gespeichert und kann jederzeit reaktiviert werden.</small></p>'
            )
            ->send();
    }

    private function sendPausedMail(JobListing $listing): void
    {
        $user = $listing->createdByUser;
        if (!$user || !$user->email) {
            return;
        }

        $reactivateUrl = \yii\helpers\Url::to(
            ['/jobs/job/reactivate', 'id' => $listing->id],
            true
        );

        Yii::$app->mailer->compose()
            ->setTo($user->email)
            ->setFrom([Yii::$app->settings->get('systemEmailAddress') => 'Stationszimmer'])
            ->setSubject('Ihr Inserat wurde pausiert – ' . $listing->title)
            ->setHtmlBody(
                '<p>Guten Tag ' . htmlspecialchars($user->displayName) . '</p>' .
                '<p>Ihr Inserat <strong>' . htmlspecialchars($listing->title) . '</strong> wurde automatisch pausiert, da wir keine Rückmeldung erhalten haben.</p>' .
                '<p>Falls die Stelle noch offen ist, können Sie es jederzeit reaktivieren:</p>' .
                '<p><a href="' . $reactivateUrl . '" style="background:#120a8f;color:#fff;padding:10px 20px;text-decoration:none;border-radius:4px;">Inserat reaktivieren</a></p>'
            )
            ->send();
    }

    /** Einfaches HMAC-Token für 1-Klick-Links. */
    private function token(JobListing $listing): string
    {
        return hash_hmac('sha256', 'job-confirm-' . $listing->id, Yii::$app->params['cookieValidationKey'] ?? 'fallback');
    }
}
