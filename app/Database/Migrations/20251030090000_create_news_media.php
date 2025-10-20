<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNewsMedia extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('news_media')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 9,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'news_id' => [
                'type'       => 'INT',
                'constraint' => 9,
                'unsigned'   => true,
            ],
            'media_type' => [
                'type'       => 'ENUM',
                'constraint' => ['image', 'video'],
            ],
            'file_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'external_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'caption' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'metadata' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'is_cover' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 5,
                'default'    => 0,
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
        $this->forge->addKey(['news_id', 'sort_order']);
        $this->forge->addForeignKey('news_id', 'news', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('news_media', true);
    }

    public function down()
    {
        $this->forge->dropTable('news_media', true);
    }
}
