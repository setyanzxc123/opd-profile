<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIconToNewsCategories extends Migration
{
    public function up()
    {
        $fields = [
            'icon' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'bx-news',
                'after'      => 'slug',
            ],
        ];

        $this->forge->addColumn('news_categories', $fields);

        // Populate default icons for common categories if they exist
        $db = \Config\Database::connect();
        $db->table('news_categories')->where('slug', 'umum')->update(['icon' => 'bx-news']);
        $db->table('news_categories')->where('slug', 'kegiatan')->update(['icon' => 'bx-calendar-event']);
        $db->table('news_categories')->where('slug', 'kebijakan')->update(['icon' => 'bx-briefcase']);
        $db->table('news_categories')->where('slug', 'prestasi')->update(['icon' => 'bx-trophy']);
        $db->table('news_categories')->where('slug', 'pengumuman')->update(['icon' => 'bx-bullhorn']);
    }

    public function down()
    {
        $this->forge->dropColumn('news_categories', 'icon');
    }
}
