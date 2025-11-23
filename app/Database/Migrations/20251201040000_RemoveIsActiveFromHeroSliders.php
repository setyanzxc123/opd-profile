<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveIsActiveFromHeroSliders extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('hero_sliders')) {
            $this->forge->dropColumn('hero_sliders', 'is_active');
        }
    }

    public function down()
    {
        if ($this->db->tableExists('hero_sliders')) {
            $this->forge->addColumn('hero_sliders', [
                'is_active' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 1,
                    'after' => 'source_ref_id'
                ]
            ]);
        }
    }
}