<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePersonasTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name'         => ['type' => 'VARCHAR', 'constraint' => 100],
            'instructions' => ['type' => 'TEXT'],
            'is_default'   => ['type' => 'BOOLEAN', 'default' => false],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->createTable('personas');
    }

    public function down(): void
    {
        $this->forge->dropTable('personas');
    }
}
