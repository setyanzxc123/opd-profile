<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateContactMessagesMetadata extends Migration
{
    public function up(): void
    {
        $db = $this->db;

        if (! $db->fieldExists('phone', 'contact_messages')) {
            $this->forge->addColumn('contact_messages', [
                'phone' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 30,
                    'null'       => true,
                    'after'      => 'email',
                ],
            ]);
        }

        if (! $db->fieldExists('ip_address', 'contact_messages')) {
            $this->forge->addColumn('contact_messages', [
                'ip_address' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 45,
                    'null'       => true,
                    'after'      => $db->fieldExists('phone', 'contact_messages') ? 'phone' : 'email',
                ],
            ]);
        }

        if (! $db->fieldExists('user_agent', 'contact_messages')) {
            $after = 'email';
            if ($db->fieldExists('ip_address', 'contact_messages')) {
                $after = 'ip_address';
            } elseif ($db->fieldExists('phone', 'contact_messages')) {
                $after = 'phone';
            }

            $this->forge->addColumn('contact_messages', [
                'user_agent' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => $after,
                ],
            ]);
        }

        if ($db->fieldExists('responded_at', 'contact_messages')) {
            $this->forge->modifyColumn('contact_messages', [
                'responded_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
        }
    }

    public function down(): void
    {
        $db = $this->db;

        foreach (['user_agent', 'ip_address', 'phone'] as $field) {
            if ($db->fieldExists($field, 'contact_messages')) {
                $this->forge->dropColumn('contact_messages', $field);
            }
        }
    }
}
