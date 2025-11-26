<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSocialMediaToProfile extends Migration
{
    public function up()
    {
        $fields = [
            'social_facebook' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'email',
            ],
            'social_instagram' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'social_facebook',
            ],
            'social_twitter' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'social_instagram',
            ],
            'social_youtube' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'social_twitter',
            ],
            'social_tiktok' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'social_youtube',
            ],
            'operational_hours' => [
                'type'       => 'TEXT',
                'null'       => true,
                'after'      => 'social_tiktok',
            ],
            'operational_notes' => [
                'type'       => 'TEXT',
                'null'       => true,
                'after'      => 'operational_hours',
            ],
        ];

        $this->forge->addColumn('opd_profile', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('opd_profile', [
            'social_facebook',
            'social_instagram',
            'social_twitter',
            'social_youtube',
            'social_tiktok',
            'operational_hours',
            'operational_notes',
        ]);
    }
}
