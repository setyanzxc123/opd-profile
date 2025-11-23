<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPracticalFeaturesHeroSliders extends Migration
{
    public function up()
    {
        // Add practical fields to hero_sliders table
        $fields = [
            'image_alt' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'image_path',
                'comment'    => 'Alt text for image (accessibility & SEO)',
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'null'       => false,
                'after'      => 'image_alt',
                'comment'    => 'Status: 1=active, 0=inactive',
            ],
            'view_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
                'after'      => 'is_active',
                'comment'    => 'Total views count',
            ],
        ];

        $this->forge->addColumn('hero_sliders', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('hero_sliders', ['image_alt', 'is_active', 'view_count']);
    }
}
