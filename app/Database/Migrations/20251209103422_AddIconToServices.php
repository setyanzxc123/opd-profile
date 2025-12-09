<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIconToServices extends Migration
{
    public function up()
    {
        $this->forge->addColumn('services', [
            'icon' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
                'after'      => 'slug', // Letakkan setelah slug atau thumbnail
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('services', 'icon');
    }
}
