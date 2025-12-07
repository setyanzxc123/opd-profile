<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration untuk menambahkan setting show_app_links ke opd_profile
 */
class AddShowAppLinksToProfile extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('opd_profile')) {
            return;
        }

        // Check if column already exists
        if ($this->db->fieldExists('show_app_links', 'opd_profile')) {
            return;
        }

        $this->forge->addColumn('opd_profile', [
            'show_app_links' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'null' => false,
                'after' => 'hide_brand_text',
                'comment' => 'Tampilkan slider tautan aplikasi di homepage',
            ],
        ]);
    }

    public function down()
    {
        if ($this->db->fieldExists('show_app_links', 'opd_profile')) {
            $this->forge->dropColumn('opd_profile', 'show_app_links');
        }
    }
}
