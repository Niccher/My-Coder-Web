<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUuidToConversations extends Migration
{
    public function up()
    {
        $this->forge->addColumn('conversations', [
            'uuid' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
            ],
        ]);

        $db = \Config\Database::connect();
        $builder = $db->table('conversations');
        $conversations = $builder->get()->getResult();
        foreach ($conversations as $c) {
            $uuid = bin2hex(random_bytes(16));
            $builder->where('id', $c->id)->update(['uuid' => $uuid]);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('conversations', 'uuid');
    }
}
