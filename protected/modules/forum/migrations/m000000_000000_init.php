<?php

use humhub\components\Migration;

/**
 * Forum-Schema: Kategorien, Themen, Beiträge. Inkl. Seed der 8 Bereiche.
 *
 * @verify: humhub\components\Migration + Tabellenname `user` gegen Installation.
 */
class m000000_000000_init extends Migration
{
    public function safeUp()
    {
        $this->createTable('forum_category', [
            'id' => $this->primaryKey(),
            'slug' => $this->string(60)->notNull()->unique(),
            'title' => $this->string(160)->notNull(),
            'description' => $this->string(400)->null(),
            'icon' => $this->string(40)->null(),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ]);

        $this->createTable('forum_thread', [
            'id' => $this->primaryKey(),
            'category_id' => $this->integer()->notNull(),
            'title' => $this->string(255)->notNull(),
            'created_by' => $this->integer()->notNull(),
            'lang' => $this->char(2)->notNull()->defaultValue('de'),
            'is_pinned' => $this->tinyInteger(1)->notNull()->defaultValue(0),
            'is_locked' => $this->tinyInteger(1)->notNull()->defaultValue(0),
            'last_post_at' => $this->dateTime()->null(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ]);

        $this->createTable('forum_post', [
            'id' => $this->primaryKey(),
            'thread_id' => $this->integer()->notNull(),
            'created_by' => $this->integer()->notNull(),
            'message' => $this->text()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ]);

        $this->createIndex('idx_forum_thread_category', 'forum_thread', 'category_id');
        $this->createIndex('idx_forum_thread_last_post', 'forum_thread', 'last_post_at');
        $this->createIndex('idx_forum_post_thread', 'forum_post', 'thread_id');

        $this->addForeignKey('fk_forum_thread_category', 'forum_thread', 'category_id', 'forum_category', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_forum_post_thread', 'forum_post', 'thread_id', 'forum_thread', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_forum_thread_user', 'forum_thread', 'created_by', 'user', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_forum_post_user', 'forum_post', 'created_by', 'user', 'id', 'CASCADE', 'CASCADE');

        // Seed: die 8 Stationszimmer-Bereiche.
        $now = date('Y-m-d H:i:s');
        $categories = [
            ['spital', 'Spital & Akutpflege', 'Austausch rund um die Akutpflege im Spital.', 'hospital-o'],
            ['spitex', 'Spitex & ambulante Pflege', 'Themen der ambulanten und häuslichen Pflege.', 'home'],
            ['langzeit', 'Langzeitpflege & Pflegeheim', 'Pflegeheim, Langzeit- und Betagtenbetreuung.', 'bed'],
            ['bildung', 'Ausbildung & Weiterbildung', 'Ausbildung, HF/FH, Weiterbildungen und Karriere.', 'graduation-cap'],
            ['stellenmarkt', 'Stellenmarkt & Networking', 'Vernetzung, Stellen und Erfahrungsaustausch.', 'users'],
            ['recht', 'Recht, GAV & Lohn', 'Arbeitsrecht, GAV, Lohn und Anstellungsbedingungen.', 'balance-scale'],
            ['politik', 'Politik & Pflegeinitiative', 'Berufspolitik, Pflegeinitiative und Gesundheitswesen.', 'bullhorn'],
            ['support', 'Support & Feedback', 'Fragen, Hinweise und Feedback zur Plattform.', 'life-ring'],
        ];
        $sort = 0;
        foreach ($categories as [$slug, $title, $desc, $icon]) {
            $this->insert('forum_category', [
                'slug' => $slug,
                'title' => $title,
                'description' => $desc,
                'icon' => $icon,
                'sort_order' => $sort++,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function safeDown()
    {
        $this->dropTable('forum_post');
        $this->dropTable('forum_thread');
        $this->dropTable('forum_category');
    }
}
