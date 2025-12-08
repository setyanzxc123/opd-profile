<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePpidTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'about' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'vision' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'mission' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'tasks_functions' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('ppid');

        // Insert default empty row
        $this->db->table('ppid')->insert([
            'about'           => null,
            'vision'          => null,
            'mission'         => null,
            'tasks_functions' => null,
            'updated_at'      => date('Y-m-d H:i:s'),
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('ppid');
    }
}
