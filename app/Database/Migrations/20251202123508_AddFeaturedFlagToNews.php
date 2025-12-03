<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFeaturedFlagToNews extends Migration
{
    public function up()
    {
        $fields = [
            'is_featured' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
                'comment'    => 'Featured news flag (0=normal, 1=featured)',
            ],
        ];

        $this->forge->addColumn('news', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('news', 'is_featured');
    }
}
