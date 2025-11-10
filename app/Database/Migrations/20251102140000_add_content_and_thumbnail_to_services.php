<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddContentAndThumbnailToServices extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('services')) {
            return;
        }

        $fields = [];

        if (! $this->db->fieldExists('content', 'services')) {
            $fields['content'] = [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'description',
            ];
        }

        if (! $this->db->fieldExists('thumbnail', 'services')) {
            $fields['thumbnail'] = [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'processing_time',
            ];
        }

        if ($fields !== []) {
            $this->forge->addColumn('services', $fields);
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('services')) {
            return;
        }

        if ($this->db->fieldExists('content', 'services')) {
            $this->forge->dropColumn('services', 'content');
        }

        if ($this->db->fieldExists('thumbnail', 'services')) {
            $this->forge->dropColumn('services', 'thumbnail');
        }
    }
}

