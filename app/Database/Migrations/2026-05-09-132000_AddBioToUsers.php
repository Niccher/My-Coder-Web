<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBioToUsers extends Migration
{
    public function up()
    {
        $fields = [
            'bio' => [
                'type'       => 'TEXT',
                'null'       => true,
                'after'      => 'avatar',
            ],
        ];
        $this->forge->addColumn('users', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'bio');
    }
}
