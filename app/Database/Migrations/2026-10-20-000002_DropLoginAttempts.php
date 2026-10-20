<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Removes login_attempts. AddLoginAttempts created it, but nothing ever read or
 * wrote it: sign-in limits are kept on the user row (failed_logins,
 * locked_until) and per connection by CodeIgniter's throttler. The migration
 * that created it stays, because databases record it as run.
 */
class DropLoginAttempts extends Migration
{
    public function up(): void
    {
        $this->forge->dropTable('login_attempts', true);
    }

    public function down(): void
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'ip'           => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => false],
            'email'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'attempted_at' => ['type' => 'DATETIME', 'null' => false],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('email');
        $this->forge->addKey('ip');
        $this->forge->createTable('login_attempts', true);
    }
}
