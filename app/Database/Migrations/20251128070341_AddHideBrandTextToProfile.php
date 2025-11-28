<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddHideBrandTextToProfile extends Migration
{
    public function up()
    {
        $this->forge->addColumn('opd_profile', [
            'hide_brand_text' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
                'after'      => 'name_line2',
                'comment'    => 'Sembunyikan nama OPD di navbar jika logo sudah ada text'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('opd_profile', 'hide_brand_text');
    }
}
