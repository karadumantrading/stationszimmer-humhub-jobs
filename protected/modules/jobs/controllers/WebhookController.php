<?php

namespace humhub\modules\jobs\controllers;

use Yii;
use humhub\modules\jobs\Module;
use humhub\modules\jobs\models\JobListing;

/**
 * Stripe-Webhook. Öffentlicher Endpunkt: kein Login, kein CSRF.
 *
 * Bewusst `yii\web\Controller` (NICHT humhub\components\Controller): so greift
 * der HumHub-Guest-/Login-Access-Layer nicht und Stripe erreicht den Endpunkt
 * unabhängig von der «Gastzugriff»-Einstellung. Die Authentizität sichert
 * AUSSCHLIESSLICH die Stripe-Signatur (\Stripe\Webhook::constructEvent).
 *
 * @verify: dass ein plain Yii-Controller im Modul für Gäste routet (Docker-Test).
 */
class WebhookController extends \yii\web\Controller
{
    public $enableCsrfValidation = false;

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
