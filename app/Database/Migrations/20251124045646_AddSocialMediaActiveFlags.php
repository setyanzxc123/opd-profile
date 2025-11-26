<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSocialMediaActiveFlags extends Migration
{
    public function up()
    {
        $fields = [
            'social_facebook_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'after'      => 'social_facebook',
            ],
            'social_instagram_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'after'      => 'social_instagram',
            ],
            'social_twitter_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'after'      => 'social_twitter',
            ],
            'social_youtube_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'after'      => 'social_youtube',
            ],
            'social_tiktok_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'after'      => 'social_tiktok',
            ],
        ];

        $this->forge->addColumn('opd_profile', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('opd_profile', [
            'social_facebook_active',
            'social_instagram_active',
            'social_twitter_active',
            'social_youtube_active',
            'social_tiktok_active',
        ]);
    }
}
