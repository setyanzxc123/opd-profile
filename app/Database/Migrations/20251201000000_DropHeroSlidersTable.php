<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropHeroSlidersTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('hero_sliders')) {
            $this->forge->dropTable('hero_sliders', true);
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('hero_sliders')) {
            $this->forge->addField([
                'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'title'       => ['type' => 'VARCHAR', 'constraint' => 255],
                'subtitle'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'description' => ['type' => 'TEXT', 'null' => true],
                'image_path'  => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
                'target_url'  => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
                'target_type' => ['type' => 'VARCHAR', 'constraint' => 50],
                'target_id'   => ['type' => 'INT', 'constraint' => 11, 'null' => true],
                'button_text' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
                'is_active'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
                'sort_order'  => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
                'start_date'  => ['type' => 'DATETIME', 'null' => true],
                'end_date'    => ['type' => 'DATETIME', 'null' => true],
                'created_at'  => ['type' => 'DATETIME', 'null' => true],
                'updated_at'  => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey(['is_active', 'sort_order']);
            $this->forge->createTable('hero_sliders');
        }
    }
}
