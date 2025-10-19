<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNewsMetadataFields extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('news', [
            'excerpt' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
                'default'    => '',
                'after'      => 'content',
            ],
            'public_author' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
                'default'    => '',
                'after'      => 'excerpt',
            ],
            'source' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
                'default'    => '',
                'after'      => 'public_author',
            ],
            'meta_title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
                'default'    => '',
                'after'      => 'source',
            ],
            'meta_description' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
                'default'    => '',
                'after'      => 'meta_title',
            ],
            'meta_keywords' => [
                'type' => 'TEXT',
                'null' => true,
                'after'=> 'meta_description',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('news', [
            'excerpt',
            'public_author',
            'source',
            'meta_title',
            'meta_description',
            'meta_keywords',
        ]);
    }
}

