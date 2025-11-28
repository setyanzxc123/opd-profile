<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNameLine2ToProfile extends Migration
{
    public function up()
    {
        $this->forge->addColumn('opd_profile', [
            'name_line2' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'name',
                'comment'    => 'Nama OPD baris kedua (opsional)'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('opd_profile', 'name_line2');
    }
}
