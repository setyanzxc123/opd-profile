<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHeroSliders extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('hero_sliders')) {
            return;
        }

        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'title'          => ['type' => 'VARCHAR', 'constraint' => 255],
            'subtitle'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'description'    => ['type' => 'TEXT', 'null' => true],
            'button_text'    => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'button_link'    => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'image_path'     => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'source_type'    => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'manual'],
            'source_ref_id'  => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'overlay_opacity'=> ['type' => 'INT', 'constraint' => 3, 'default' => 50],
            'is_active'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'sort_order'     => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['is_active', 'sort_order']);
        $this->forge->createTable('hero_sliders', true);
    }

    public function down()
    {
        $this->forge->dropTable('hero_sliders', true);
    }
}
