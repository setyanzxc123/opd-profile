<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveOverlayOpacityFromHeroSliders extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('hero_sliders')) {
            $this->forge->dropColumn('hero_sliders', 'overlay_opacity');
        }
    }

    public function down()
    {
        if ($this->db->tableExists('hero_sliders')) {
            $this->forge->addColumn('hero_sliders', [
                'overlay_opacity' => [
                    'type' => 'INT',
                    'constraint' => 3,
                    'default' => 50,
                    'after' => 'source_ref_id'
                ]
            ]);
        }
    }
}