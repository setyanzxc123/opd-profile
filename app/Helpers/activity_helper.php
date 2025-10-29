<?php

use App\Models\ActivityLogModel;

if (! function_exists('log_activity')) {
    function log_activity(string $action, string $description = '', ?int $userId = null): void
    {
        try {
            if ($userId === null && function_exists('auth')) {
                $auth = auth('session');
                if ($auth->loggedIn()) {
                    $userId = (int) ($auth->user()->id ?? 0);
                }
            }

            $userId = $userId ?? (int) session('user_id');

            if (! $userId) {
                return;
            }

            $model = model(ActivityLogModel::class);
            $model->insertLog([
                'user_id'    => $userId,
                'action'     => $action,
                'description'=> $description,
            ]);
        } catch (\Throwable $e) {
            log_message('warning', 'Failed to log activity: {error}', ['error' => $e->getMessage()]);
        }
    }
}
