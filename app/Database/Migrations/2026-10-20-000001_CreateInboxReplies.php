<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Replies sent from the Inbox, so the team can see what was answered, by whom
 * and when. One row per email sent; `type` + `message_id` point at the
 * enquiry or job application it answers.
 */
class CreateInboxReplies extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'type'       => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => false],
            'message_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => false],
            'user_id'    => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'user_name'  => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false, 'default' => ''],
            'to_email'   => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'subject'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'body'       => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['type', 'message_id']);
        $this->forge->createTable('inbox_replies', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('inbox_replies', true);
    }
}
