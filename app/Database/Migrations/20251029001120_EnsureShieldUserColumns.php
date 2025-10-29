<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EnsureShieldUserColumns extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('users')) {
            return;
        }

        $fields = [];

        if (! $this->db->fieldExists('email', 'users')) {
            $fields['email'] = [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ];
        }

        if (! $this->db->fieldExists('password_hash', 'users')) {
            $fields['password_hash'] = [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ];
        }

        if (! $this->db->fieldExists('role', 'users')) {
            $fields['role'] = [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'default'    => 'editor',
            ];
        }

        if (! $this->db->fieldExists('is_active', 'users')) {
            $fields['is_active'] = [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'default'    => 1,
            ];
        }

        if (! $this->db->fieldExists('last_login_at', 'users')) {
            $fields['last_login_at'] = [
                'type' => 'DATETIME',
                'null' => true,
            ];
        }

        if ($fields !== []) {
            $this->forge->addColumn('users', $fields);
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('users')) {
            return;
        }

        foreach (['last_login_at', 'is_active', 'role', 'password_hash', 'email'] as $column) {
            if ($this->db->fieldExists($column, 'users')) {
                $this->forge->dropColumn('users', $column);
            }
        }
    }
}
