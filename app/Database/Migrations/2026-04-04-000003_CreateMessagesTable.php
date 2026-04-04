<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMessagesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'conversation_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'role'            => ['type' => 'ENUM', 'constraint' => ['user', 'ai'], 'default' => 'user'],
            'model_name'      => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'comment' => 'null for user messages'],
            'content'         => ['type' => 'LONGTEXT'],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('conversation_id');
        $this->forge->createTable('messages');
    }

    public function down(): void
    {
        $this->forge->dropTable('messages');
    }
}
