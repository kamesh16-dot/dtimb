<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Sign-in attempts table. Recreated from the live database under its original
 * version and name (the file was missing from the project); created only if
 * missing.
 */
class AddLoginAttempts extends Migration
{
    public function up(): void
    {
        // ── login_attempts
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'ip' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => false],
            'email' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'attempted_at' => ['type' => 'DATETIME', 'null' => false],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('email');
        $this->forge->addKey('ip');
        $this->forge->createTable('login_attempts', true);

    }

    public function down(): void
    {
        $this->forge->dropTable('login_attempts', true);
    }
}
