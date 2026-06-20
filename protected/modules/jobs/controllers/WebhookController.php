<?php

namespace humhub\modules\jobs\controllers;

use Yii;
use humhub\components\Controller;
use humhub\components\access\ControllerAccess;
use humhub\modules\jobs\Module;
use humhub\modules\jobs\models\JobListing;

/**
 * Stripe-Webhook. Erreichbar OHNE Login und OHNE CSRF, aber es wird
 * AUSSCHLIESSLICH signaturgeprüften Events vertraut (\Stripe\Webhook::constructEvent).
 *
 * @verify: Guest-Zugriff/Access-Layer gegen installierte HumHub-Version.
 */
class WebhookController extends Controller
{
    public $enableCsrfValidation = false;
    public $access = ControllerAccess::class;

    protected function getAccessRules(): array
    {
        // Gast erlaubt – die Authentizität sichert die Stripe-Signatur, nicht das Login.
        return [
            [ControllerAccess::RULE_GUEST_ACCESS_ONLY => ['index']],
        ];
    }

    /** @return Module */
    private function module(): Module
    {
        return Yii::$app->getModule('jobs');
    }

    public function actionIndex()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;

        $webhookSecret = Yii::$app->params['stripe']['webhookSecret'] ?? null;
        if (empty($webhookSecret)) {
            Yii::$app->response->statusCode = 503;
            return '';
        }

        $payload = Yii::$app->request->getRawBody();
        $sig = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sig, $webhookSecret);
        } catch (\Throwable $e) {
            // Ungültige Signatur / Payload -> ablehnen, NICHT verarbeiten.
            Yii::$app->response->statusCode = 400;
            return '';
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $listingId = (int) ($session->metadata->listing_id ?? 0);
            $listing = $listingId ? JobListing::findOne($listingId) : null;

            // Idempotenz: nur verarbeiten, wenn noch nicht veröffentlicht/freigegeben.
            if ($listing !== null
                && in_array($listing->status, [JobListing::STATUS_PENDING_PAYMENT, JobListing::STATUS_DRAFT], true)) {

                $listing->updateAttributes([
                    'stripe_payment_intent_id' => $session->payment_intent ?? null,
                ]);

                if ($this->module()->isModerationEnabled()) {
                    $listing->markPendingReview();
                } else {
                    $listing->publish();
                }
            }
        }

        Yii::$app->response->statusCode = 200;
        return '';
    }
}
