<?php

namespace App\Models;

use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;
use Config\Services;
use CodeIgniter\Shield\Entities\User;

class UserModel extends ShieldUserModel
{
    protected $allowedFields = [
        'username',
        'status',
        'status_message',
        'active',
        'last_active',
        'last_login_at',
        'deleted_at',
        'name',
        'role',
        'email',
        'is_active',
        'password_hash',
        'created_at',
        'updated_at',
    ];

    public const ROLES = ['admin', 'editor'];

    protected function initialize(): void
    {
        parent::initialize();

        $this->afterInsert[] = 'syncLegacyFlagAfterSave';
        $this->afterUpdate[] = 'syncLegacyFlagAfterSave';
    }

    /**
     * Persist a password hash onto the entity and legacy column.
     */
    public function withPassword(User $user, string $password): User
    {
        $hash = service('passwords')->hash($password);
        $user->setPassword($password);
        $user->setPasswordHash($hash);

        return $user;
    }

    /**
     * Model event callback to keep legacy columns in sync.
     *
     * @param array{id?: int|string|array<int|string>, data?: array} $data
     */
    protected function syncLegacyFlagAfterSave(array $data): array
    {
        if (! isset($data['id'])) {
            return $data;
        }

        $ids = is_array($data['id']) ? $data['id'] : [$data['id']];
        foreach ($ids as $id) {
            $this->syncLegacyActiveFlag($id);
        }

        return $data;
    }

    private function syncLegacyActiveFlag(int|string $id): void
    {
        $db = $this->db ?? Services::database();

        if ($db->fieldExists('is_active', $this->table)) {
            $db->table($this->table)
                ->set('is_active', 'active', false)
                ->where('id', $id)
                ->update();
        }
    }
}
