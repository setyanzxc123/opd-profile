<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGreetingAndTasksToProfile extends Migration
{
    public function up()
    {
        $fields = [
            'greeting' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'mission',
            ],
            'tasks_functions' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'greeting',
            ],
        ];

        $this->forge->addColumn('opd_profile', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('opd_profile', ['greeting', 'tasks_functions']);
    }
}
