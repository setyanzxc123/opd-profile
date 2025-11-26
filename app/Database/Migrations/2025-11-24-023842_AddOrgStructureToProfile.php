<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOrgStructureToProfile extends Migration
{
    public function up()
    {
        $this->forge->addColumn('opd_profile', [
            'org_structure_image' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'email',
            ],
            'org_structure_alt_text' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'org_structure_image',
            ],
            'org_structure_updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'org_structure_alt_text',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('opd_profile', [
            'org_structure_image',
            'org_structure_alt_text',
            'org_structure_updated_at',
        ]);
    }
}
