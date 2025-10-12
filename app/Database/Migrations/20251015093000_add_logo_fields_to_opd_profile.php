<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLogoFieldsToOpdProfile extends Migration
{
    private const TABLE = 'opd_profile';

    public function up()
    {
        $fields = $this->db->getFieldNames(self::TABLE);

        $columnsToAdd = [];

        if (! in_array('logo_public_path', $fields, true)) {
            $columnsToAdd['logo_public_path'] = [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'email',
            ];
        }

        if (! in_array('logo_admin_path', $fields, true)) {
            $columnsToAdd['logo_admin_path'] = [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'logo_public_path',
            ];
        }

        if ($columnsToAdd !== []) {
            $this->forge->addColumn(self::TABLE, $columnsToAdd);
        }
    }

    public function down()
    {
        $fields = $this->db->getFieldNames(self::TABLE);
        $columnsToDrop = [];

        if (in_array('logo_admin_path', $fields, true)) {
            $columnsToDrop[] = 'logo_admin_path';
        }

        if (in_array('logo_public_path', $fields, true)) {
            $columnsToDrop[] = 'logo_public_path';
        }

        foreach ($columnsToDrop as $column) {
            $this->forge->dropColumn(self::TABLE, $column);
        }
    }
}

