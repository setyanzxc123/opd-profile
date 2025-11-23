<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Hero Sliders Table Migration
 * 
 * Membuat tabel hero_sliders untuk menyimpan data slider
 * Pastikan tabel ini ada sebelum menggunakan modul hero slider
 */
class CreateHeroSlidersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
            ],
            'subtitle' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'button_text' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
            'button_link' => [
                'type'       => 'VARCHAR',
                'constraint' => '500',
                'null'       => true,
            ],
            'image_path' => [
                'type'       => 'VARCHAR',
                'constraint' => '500',
                'null'       => true,
            ],
            'image_alt' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'source_type' => [
                'type'       => 'ENUM',
                'constraint' => ['manual', 'internal'],
                'default'    => 'manual',
                'null'       => false,
            ],
            'source_ref_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
                'comment'    => 'Reference to internal content (e.g., news:123, service:456)',
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'null'       => false,
            ],
            'view_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'unsigned'   => true,
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'unsigned'   => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('is_active');
        $this->forge->addKey('sort_order');
        $this->forge->addKey('created_at');
        
        $this->forge->createTable('hero_sliders', true);
    }

    public function down()
    {
        $this->forge->dropTable('hero_sliders', true);
    }
}
