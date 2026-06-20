<?php

namespace humhub\modules\jobs\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\ServerErrorHttpException;
use humhub\components\Controller;
use humhub\components\access\ControllerAccess;
use humhub\modules\jobs\Module;
use humhub\modules\jobs\models\JobListing;

/**
 * Tarifwahl & Stripe Checkout.
 *
 * Serverhoheit: Tier, Price-ID und Dauer kommen ausschliesslich aus der
 * Modul-Config; der Client wählt nur den Tier-Schlüssel, der Server validiert
 * die Erlaubnis (Intro nur vor Stichdatum, bezahlte nur mit Price-ID).
 */
class CheckoutController extends Controller
{
    public $access = ControllerAccess::class;

    protected function getAccessRules(): array
    {
        // success kommt von Stripe zurück – Login dort nicht erzwingen.
        return [
            [ControllerAccess::RULE_LOGGED_IN_ONLY => ['select', 'checkout', 'cancel']],
        ];
    }

    /** @return Module */
    private function module(): Module
    {
        return Yii::$app->getModule('jobs');
    }

    private function findOwnDraft(int $id): JobListing
    {
        $listing = JobListing::findOne($id);
        if ($listing === null) {
            throw new NotFoundHttpException(Yii::t('JobsModule.base', 'Inserat nicht gefunden.'));
        }
        if (!$listing->canManage()) {
            throw new ForbiddenHttpException(Yii::t('JobsModule.base', 'Keine Berechtigung für dieses Inserat.'));
        }
        return $listing;
    }

    /** Tarif-Auswahl anzeigen (nur aktuell erlaubte Tiers). */
    public function actionSelect($id)
    {
        $listing = $this->findOwnDraft((int) $id);

        return $this->render('select', [
            'listing' => $listing,
            'tiers' => $this->module()->getAvailableTiers(),
        ]);
    }

    /**
     * Tarif verarbeiten: Gratis-Tier direkt (oder zur Moderation), bezahlte Tiers
     * über Stripe Checkout.
     */
    public function actionCheckout($id)
    {
        $this->forcePostRequest();
        $listing = $this->findOwnDraft((int) $id);

        $tier = (string) Yii::$app->request->post('tier', '');

        // Serverseitige Erlaubnisprüfung – niemals dem Client vertrauen.
        if (!$this->module()->isTierAvailable($tier)) {
            throw new ForbiddenHttpException(Yii::t('JobsModule.base', 'Dieser Tarif ist nicht (mehr) verfügbar.'));
        }

        $def = $this->module()->getTier($tier);
        $listing->updateAttributes(['tier' => $tier]);

        // Gratis-Tier (Lehrstelle): kein Stripe.
        if (!empty($def['free'])) {
            if ($this->module()->isModerationEnabled()) {
                $listing->markPendingReview();
                $this->view->success(Yii::t('JobsModule.base', 'Inserat eingereicht – es wird nach kurzer Prüfung veröffentlicht.'));
            } else {
                $listing->publish();
                $this->view->success(Yii::t('JobsModule.base', 'Inserat ist online.'));
            }
            return $this->redirect(['/jobs/job/my']);
        }

        // Bezahlter Tier: Stripe-Checkout-Session erzeugen.
        $secretKey = Yii::$app->params['stripe']['secretKey'] ?? null;
        if (empty($secretKey) || empty($def['stripePriceId'])) {
            throw new ServerErrorHttpException(Yii::t('JobsModule.base', 'Bezahlung ist noch nicht konfiguriert.'));
        }

        try {
            \Stripe\Stripe::setApiKey($secretKey);
            $session = \Stripe\Checkout\Session::create([
                'mode' => 'payment',
                'line_items' => [['price' => $def['stripePriceId'], 'quantity' => 1]],
                'client_reference_id' => (string) $listing->id,
                'metadata' => ['listing_id' => (string) $listing->id, 'tier' => $tier],
                'success_url' => Url::to(['/jobs/checkout/success'], true) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => Url::to(['/jobs/checkout/cancel', 'id' => $listing->id], true),
            ]);
        } catch (\Throwable $e) {
            Yii::error('Stripe Checkout fehlgeschlagen: ' . $e->getMessage(), 'jobs');
            throw new ServerErrorHttpException(Yii::t('JobsModule.base', 'Die Zahlung konnte nicht gestartet werden.'));
        }

        $listing->updateAttributes([
            'status' => JobListing::STATUS_PENDING_PAYMENT,
            'stripe_checkout_session_id' => $session->id,
        ]);

        return $this->redirect($session->url);
    }

    /** Rückkehr nach erfolgreicher Zahlung (Veröffentlichung erfolgt im Webhook). */
    public function actionSuccess()
    {
        $moderation = $this->module()->isModerationEnabled();
        return $this->render('success', ['moderation' => $moderation]);
    }

    /** Abbruch – Inserat bleibt Entwurf. */
    public function actionCancel($id)
    {
        $listing = $this->findOwnDraft((int) $id);
        if ($listing->status === JobListing::STATUS_PENDING_PAYMENT) {
            $listing->updateAttributes(['status' => JobListing::STATUS_DRAFT]);
        }
        $this->view->info(Yii::t('JobsModule.base', 'Zahlung abgebrochen – dein Inserat ist als Entwurf gespeichert.'));
        return $this->redirect(['/jobs/job/my']);
    }
}
