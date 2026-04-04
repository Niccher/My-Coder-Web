<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserSettingsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'model_slot'      => ['type' => 'TINYINT', 'constraint' => 1, 'comment' => '1, 2, or 3'],
            'model_name'      => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'api_key'         => ['type' => 'TEXT', 'null' => true, 'comment' => 'Encrypted API key'],
            'provider'        => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'comment' => 'gemini|deepseek|grok'],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['user_id', 'model_slot']);
        $this->forge->createTable('user_settings');
    }

    public function down(): void
    {
        $this->forge->dropTable('user_settings');
    }
}
