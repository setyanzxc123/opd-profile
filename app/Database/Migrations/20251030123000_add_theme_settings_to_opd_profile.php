<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddThemeSettingsToOpdProfile extends Migration
{
    private const TABLE = 'opd_profile';
    private const COLUMN = 'theme_settings';

    public function up()
    {
        $fields = $this->db->getFieldNames(self::TABLE);

        if (in_array(self::COLUMN, $fields, true)) {
            return;
        }

        $this->forge->addColumn(self::TABLE, [
            self::COLUMN => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'logo_admin_path',
            ],
        ]);
    }

    public function down()
    {
        $fields = $this->db->getFieldNames(self::TABLE);

        if (! in_array(self::COLUMN, $fields, true)) {
            return;
        }

        $this->forge->dropColumn(self::TABLE, self::COLUMN);
    }
}

