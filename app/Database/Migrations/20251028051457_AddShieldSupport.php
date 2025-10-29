<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class AddShieldSupport extends Migration
{
    public function up()
    {
        $this->db->transStart();

        $this->ensureUserColumns();
        $this->createAuthIdentities();
        $this->createAuthLogins();
        $this->createAuthTokenLogins();
        $this->createAuthRememberTokens();
        $this->createAuthGroupsUsers();
        $this->createAuthPermissionsUsers();

        $this->syncLegacyUserFlags();

        $this->db->transComplete();
    }

    public function down()
    {
        $this->db->transStart();

        $this->dropTableIfExists('auth_permissions_users');
        $this->dropTableIfExists('auth_groups_users');
        $this->dropTableIfExists('auth_remember_tokens');
        $this->dropTableIfExists('auth_token_logins');
        $this->dropTableIfExists('auth_logins');
        $this->dropTableIfExists('auth_identities');

        $this->dropUserColumn('deleted_at');
        $this->dropUserColumn('last_active');
        $this->dropUserColumn('active');
        $this->dropUserColumn('status_message');
        $this->dropUserColumn('status');

        $this->db->transComplete();
    }

    private function ensureUserColumns(): void
    {
        $fields = [];

        if (! $this->db->fieldExists('status', 'users')) {
            $fields['status'] = [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ];
        }

        if (! $this->db->fieldExists('status_message', 'users')) {
            $fields['status_message'] = [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ];
        }

        if (! $this->db->fieldExists('active', 'users')) {
            $fields['active'] = [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'default'    => 1,
            ];
        }

        if (! $this->db->fieldExists('last_active', 'users')) {
            $fields['last_active'] = [
                'type' => 'DATETIME',
                'null' => true,
            ];
        }

        if (! $this->db->fieldExists('deleted_at', 'users')) {
            $fields['deleted_at'] = [
                'type' => 'DATETIME',
                'null' => true,
            ];
        }

        if ($fields !== []) {
            $this->forge->addColumn('users', $fields);
        }
    }

    private function createAuthIdentities(): void
    {
        if ($this->db->tableExists('auth_identities')) {
            return;
        }

        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'type'         => ['type' => 'VARCHAR', 'constraint' => 255],
            'name'         => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'secret'       => ['type' => 'VARCHAR', 'constraint' => 255],
            'secret2'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'expires'      => ['type' => 'DATETIME', 'null' => true],
            'extra'        => ['type' => 'TEXT', 'null' => true],
            'force_reset'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'last_used_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['type', 'secret']);
        $this->forge->addKey('user_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('auth_identities', true);
    }

    private function createAuthLogins(): void
    {
        if ($this->db->tableExists('auth_logins')) {
            return;
        }

        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => 255],
            'user_agent' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'id_type'    => ['type' => 'VARCHAR', 'constraint' => 255],
            'identifier' => ['type' => 'VARCHAR', 'constraint' => 255],
            'user_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'date'       => ['type' => 'DATETIME', 'default' => new RawSql('CURRENT_TIMESTAMP')],
            'success'    => ['type' => 'TINYINT', 'constraint' => 1],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['id_type', 'identifier']);
        $this->forge->addKey('user_id');
        $this->forge->createTable('auth_logins', true);
    }

    private function createAuthTokenLogins(): void
    {
        if ($this->db->tableExists('auth_token_logins')) {
            return;
        }

        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => 255],
            'user_agent' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'id_type'    => ['type' => 'VARCHAR', 'constraint' => 255],
            'identifier' => ['type' => 'VARCHAR', 'constraint' => 255],
            'user_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'date'       => ['type' => 'DATETIME', 'default' => new RawSql('CURRENT_TIMESTAMP')],
            'success'    => ['type' => 'TINYINT', 'constraint' => 1],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['id_type', 'identifier']);
        $this->forge->addKey('user_id');
        $this->forge->createTable('auth_token_logins', true);
    }

    private function createAuthRememberTokens(): void
    {
        if ($this->db->tableExists('auth_remember_tokens')) {
            return;
        }

        $this->forge->addField([
            'id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'selector'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'hashedValidator' => ['type' => 'VARCHAR', 'constraint' => 255],
            'user_id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'expires'         => ['type' => 'DATETIME'],
            'created_at'      => ['type' => 'DATETIME', 'default' => new RawSql('CURRENT_TIMESTAMP')],
            'updated_at'      => ['type' => 'DATETIME', 'default' => new RawSql('CURRENT_TIMESTAMP')],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('selector');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('auth_remember_tokens', true);
    }

    private function createAuthGroupsUsers(): void
    {
        if ($this->db->tableExists('auth_groups_users')) {
            return;
        }

        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'group'      => ['type' => 'VARCHAR', 'constraint' => 255],
            'created_at' => ['type' => 'DATETIME', 'default' => new RawSql('CURRENT_TIMESTAMP')],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('auth_groups_users', true);
    }

    private function createAuthPermissionsUsers(): void
    {
        if ($this->db->tableExists('auth_permissions_users')) {
            return;
        }

        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'permission' => ['type' => 'VARCHAR', 'constraint' => 255],
            'created_at' => ['type' => 'DATETIME', 'default' => new RawSql('CURRENT_TIMESTAMP')],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('auth_permissions_users', true);
    }

    private function dropTableIfExists(string $table): void
    {
        if ($this->db->tableExists($table)) {
            $this->forge->dropTable($table, true);
        }
    }

    private function dropUserColumn(string $column): void
    {
        if ($this->db->fieldExists($column, 'users')) {
            $this->forge->dropColumn('users', $column);
        }
    }

    private function syncLegacyUserFlags(): void
    {
        if (! $this->db->fieldExists('active', 'users')) {
            return;
        }

        $builder = $this->db->table('users');

        if ($this->db->fieldExists('is_active', 'users')) {
            $builder->set('active', 'is_active', false);
            if ($this->db->fieldExists('status', 'users')) {
                $builder->set('status', "CASE WHEN is_active = 1 THEN 'active' ELSE 'inactive' END", false);
            }
            $builder->update();
        } elseif ($this->db->fieldExists('status', 'users')) {
            $builder->set('status', 'active');
            $builder->set('active', 1);
            $builder->update();
        }

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

        $now     = date('Y-m-d H:i:s');
        $builder = $this->db->table('auth_groups_users');

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
}
