<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class AddNewsTaxonomy extends Migration
{
    public function up()
    {
        // news_categories
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 9,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
            ],
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'parent_id' => [
                'type'       => 'INT',
                'constraint' => 9,
                'unsigned'   => true,
                'null'       => true,
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 4,
                'default'    => 0,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'default'    => 1,
            ],
            'created_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => false,
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('slug', false, true);
        $this->forge->addKey('parent_id');
        $this->forge->addForeignKey('parent_id', 'news_categories', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('news_categories', true);

        // news_tags
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 9,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
            ],
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'created_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => false,
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('slug', false, true);
        $this->forge->createTable('news_tags', true);

        // add primary_category_id to news
        $this->forge->addColumn('news', [
            'primary_category_id' => [
                'type'       => 'INT',
                'constraint' => 9,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'author_id',
            ],
        ]);
        $this->db->query('ALTER TABLE `news` ADD CONSTRAINT `fk_news_primary_category` FOREIGN KEY (`primary_category_id`) REFERENCES `news_categories`(`id`) ON DELETE SET NULL ON UPDATE CASCADE');

        // news_category_map
        $this->forge->addField([
            'news_id' => [
                'type'       => 'INT',
                'constraint' => 9,
                'unsigned'   => true,
            ],
            'category_id' => [
                'type'       => 'INT',
                'constraint' => 9,
                'unsigned'   => true,
            ],
        ]);
        $this->forge->addKey(['news_id', 'category_id'], true);
        $this->forge->addKey('category_id');
        $this->forge->addForeignKey('news_id', 'news', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('category_id', 'news_categories', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('news_category_map', true);

        // news_tag_map
        $this->forge->addField([
            'news_id' => [
                'type'       => 'INT',
                'constraint' => 9,
                'unsigned'   => true,
            ],
            'tag_id' => [
                'type'       => 'INT',
                'constraint' => 9,
                'unsigned'   => true,
            ],
        ]);
        $this->forge->addKey(['news_id', 'tag_id'], true);
        $this->forge->addKey('tag_id');
        $this->forge->addForeignKey('news_id', 'news', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('tag_id', 'news_tags', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('news_tag_map', true);
    }

    public function down()
    {
        $this->forge->dropTable('news_tag_map', true);
        $this->forge->dropTable('news_category_map', true);
        $this->forge->dropTable('news_tags', true);
        $this->forge->dropForeignKey('news', 'fk_news_primary_category');
        $this->forge->dropColumn('news', 'primary_category_id');
        $this->forge->dropTable('news_categories', true);
    }
}
