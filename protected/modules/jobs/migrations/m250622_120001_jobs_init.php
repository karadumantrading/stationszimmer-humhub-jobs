<?php

use humhub\components\Migration;

/**
 * Initiale Tabelle `job_listing`.
 *
 * @verify: humhub\components\Migration gegen installierte Version.
 */
class m250622_120001_jobs_init extends Migration
{
    public function safeUp()
    {
        $this->createTable('job_listing', [
            'id' => $this->primaryKey(),
            'created_by' => $this->integer()->notNull(),
            'company_name' => $this->string(255)->notNull(),
            'title' => $this->string(255)->notNull(),
            'setting' => $this->string(40)->notNull(),
            'canton' => $this->char(2)->notNull(),
            'pensum_min' => $this->smallInteger()->null(),
            'pensum_max' => $this->smallInteger()->null(),
            'employment_type' => $this->string(40)->notNull(),
            'description' => $this->text()->notNull(),
            'contact_email' => $this->string(190)->null(),
            'contact_url' => $this->string(255)->null(),
            'location' => $this->string(190)->null(),
            'tier' => $this->string(20)->null(),
            'is_top' => $this->tinyInteger(1)->notNull()->defaultValue(0),
            'status' => $this->string(20)->notNull()->defaultValue('draft'),
            'stripe_checkout_session_id' => $this->string(255)->null(),
            'stripe_payment_intent_id' => $this->string(255)->null(),
            'published_at' => $this->dateTime()->null(),
            'published_until' => $this->dateTime()->null(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ]);

        $this->createIndex('idx_job_listing_status', 'job_listing', 'status');
        $this->createIndex('idx_job_listing_canton', 'job_listing', 'canton');
        $this->createIndex('idx_job_listing_setting', 'job_listing', 'setting');
        $this->createIndex('idx_job_listing_published_until', 'job_listing', 'published_until');

        // FK auf user.id; ON DELETE CASCADE entfernt Inserate gelöschter Nutzer.
        // @verify: Tabellenname `user` in der Installation.
        $this->addForeignKey(
            'fk_job_listing_user',
            'job_listing',
            'created_by',
            'user',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable('job_listing');
    }
}
