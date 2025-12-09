<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ModifyIconInServices extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('services', [
            'icon' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('services', [
            'icon' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
        ]);
    }
}
