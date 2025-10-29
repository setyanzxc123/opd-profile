<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SyncShieldGroups extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('auth_groups_users') || ! $this->db->fieldExists('role', 'users')) {
            return;
        }

        $users = $this->db->table('users')->select('id, role')->get()->getResultArray();
        if ($users === []) {
            return;
        }

        $existing = $this->db->table('auth_groups_users')->select('user_id, `group`')->get()->getResultArray();
        $assigned = [];
        foreach ($existing as $row) {
            $assigned[$row['user_id'] . '|' . $row['group']] = true;
        }

        $builder = $this->db->table('auth_groups_users');
        $now     = date('Y-m-d H:i:s');

        foreach ($users as $user) {
            $group = strtolower((string) ($user['role'] ?? ''));
            if ($group !== 'admin') {
                $group = 'editor';
            }

            $key = $user['id'] . '|' . $group;
            if (isset($assigned[$key])) {
                continue;
            }

            $builder->insert([
                'user_id'    => $user['id'],
                'group'      => $group,
                'created_at' => $now,
            ]);
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('auth_groups_users')) {
            return;
        }

        $this->db->table('auth_groups_users')
            ->whereIn('group', ['admin', 'editor'])
            ->delete();
    }
}
