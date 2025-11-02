<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ActivityLogModel;
use App\Models\UserModel;

class ActivityLogs extends BaseController
{
    protected ActivityLogModel $logs;
    protected UserModel $users;

    public function __construct()
    {
        $this->logs  = new ActivityLogModel();
        $this->users = new UserModel();
    }

    public function index()
    {
        helper('form');

        $filters = [
            'user_id'   => (int) $this->request->getGet('user_id'),
            'date_from' => $this->request->getGet('date_from'),
            'date_to'   => $this->request->getGet('date_to'),
        ];

        $builder = $this->logs->builder()
            ->select('activity_logs.*, users.username')
            ->join('users', 'users.id = activity_logs.user_id', 'left')
            ->orderBy('activity_logs.created_at', 'DESC');

        if (! empty($filters['user_id'])) {
            $builder->where('activity_logs.user_id', $filters['user_id']);
        }

        if (! empty($filters['date_from'])) {
            $builder->where('DATE(activity_logs.created_at) >=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $builder->where('DATE(activity_logs.created_at) <=', $filters['date_to']);
        }

        $logs = $builder->get(200)->getResultArray();

        $userEntities = $this->users->orderBy('username')->findAll();
        $users        = array_map(static function ($user) {
            if (is_array($user)) {
                return [
                    'id'       => $user['id'] ?? null,
                    'username' => $user['username'] ?? '',
                ];
            }

            return [
                'id'       => $user->id ?? null,
                'username' => $user->username ?? '',
            ];
        }, $userEntities);

        return view('admin/activity_logs/index', [
            'title'   => 'Activity Logs',
            'logs'    => $logs,
            'users'   => $users,
            'filters' => $filters,
        ]);
    }
}
