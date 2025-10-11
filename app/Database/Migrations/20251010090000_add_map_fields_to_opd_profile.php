<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMapFieldsToOpdProfile extends Migration
{
    public function up()
    {
        $fields = [
            'latitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,7',
                'null'       => true,
                'after'      => 'address',
            ],
            'longitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,7',
                'null'       => true,
                'after'      => 'latitude',
            ],
            'map_zoom' => [
                'type'       => 'TINYINT',
                'constraint' => 2,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'longitude',
            ],
            'map_display' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'null'       => false,
                'default'    => 0,
                'after'      => 'map_zoom',
            ],
        ];

        $this->forge->addColumn('opd_profile', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('opd_profile', ['latitude', 'longitude', 'map_zoom', 'map_display']);
    }
}

