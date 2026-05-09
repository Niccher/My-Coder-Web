<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFoldersTable extends Migration
{
    public function up()
    {
        // Create folders table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->createTable('folders', true); // true = IF NOT EXISTS

        // Add folder_id to conversations
        $fields = [
            'folder_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'user_id'
            ],
        ];

        // Check if column exists to avoid errors if the "funny link" was already clicked
        if (!$this->db->fieldExists('folder_id', 'conversations')) {
            $this->forge->addColumn('conversations', $fields);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('folder_id', 'conversations')) {
            $this->forge->dropColumn('conversations', 'folder_id');
        }
        $this->forge->dropTable('folders', true);
    }
}
