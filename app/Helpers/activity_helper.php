<?php\n\nuse App\\Models\\ActivityLogModel;\nuse Throwable;

if (! function_exists('log_activity')) {
    function log_activity(string $action, string $description = '', ?int $userId = null): void
    {
        try {
            $session = session();
            $userId = $userId ?? $session->get('user_id');

            if (! $userId) {
                return;
            }

            $model = model(ActivityLogModel::class);
            $model->insertLog([
                'user_id'    => $userId,
                'action'     => $action,
                'description'=> $description,
            ]);
        } catch (Throwable $e) {
            log_message('warning', 'Failed to log activity: {error}', ['error' => $e->getMessage()]);
        }
    }
}

