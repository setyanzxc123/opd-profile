<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIconPathToOpdProfile extends Migration
{
    public function up()
    {
        $this->forge->addColumn('opd_profile', [
            'icon_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'logo_admin_path',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('opd_profile', 'icon_path');
    }
}
