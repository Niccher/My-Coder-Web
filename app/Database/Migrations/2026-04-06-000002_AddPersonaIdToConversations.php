<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPersonaIdToConversations extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('conversations', [
            'persona_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'after' => 'user_id'],
        ]);
        
        $this->forge->addForeignKey('persona_id', 'personas', 'id', 'SET NULL', 'SET NULL');
    }

    public function down(): void
    {
        $this->forge->dropForeignKey('conversations', 'conversations_persona_id_foreign');
        $this->forge->dropColumn('conversations', 'persona_id');
    }
}
