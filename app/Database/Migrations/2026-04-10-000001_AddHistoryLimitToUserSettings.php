<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddHistoryLimitToUserSettings extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('user_settings', [
            'history_limit' => [
                'type'       => 'SMALLINT',
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'comment'    => 'Max messages to include as history. NULL = unlimited.',
                'after'      => 'api_key',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('user_settings', 'history_limit');
    }
}
