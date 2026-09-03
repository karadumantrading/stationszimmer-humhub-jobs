<?php

use humhub\components\Migration;

/**
 * Fügt Felder für 90-Tage-Reminder und Pausierung hinzu.
 * published_until bleibt nullable (null = unbegrenzt bei Einzelinserat/Lehrstelle).
 */
class m250830_100000_jobs_reminder extends Migration
{
    public function safeUp()
    {
        // Reminder-Zeitstempel: wann wurde die letzte Bestätigungsmail gesendet?
        $this->addColumn('job_listing', 'confirmation_sent_at', $this->dateTime()->null()->after('published_until'));

        // Pausiert-Zeitstempel: wann wurde das Inserat automatisch pausiert?
        $this->addColumn('job_listing', 'paused_at', $this->dateTime()->null()->after('confirmation_sent_at'));

        // Status 'paused' hinzufügen (kein ENUM-Constraint in MySQL → einfach nutzen)
        // Status-Whitelist wird im Model gepflegt.

        // Flat-Kontext: welchem Flat-Abo-Kauf gehört dieses Inserat an?
        // null = Einzelinserat oder Lehrstelle
        $this->addColumn('job_listing', 'flat_subscription_id', $this->string(255)->null()->after('stripe_payment_intent_id'));
    }

    public function safeDown()
    {
        $this->dropColumn('job_listing', 'confirmation_sent_at');
        $this->dropColumn('job_listing', 'paused_at');
        $this->dropColumn('job_listing', 'flat_subscription_id');
    }
}
