<?php

use CodeIgniter\I18n\Time;

if (! function_exists('notify_contact_message')) {
    /**
     * Placeholder hook for contact message notifications.
     *
     * @param string $event   e.g. new_submission, status_updated
     * @param array  $payload sanitized message data
     */
    function notify_contact_message(string $event, array $payload): void
    {
        try {
            $notifyEmail   = getenv('CONTACT_NOTIFY_EMAIL');
            $notifyChannel = getenv('CONTACT_NOTIFY_TELEGRAM');

            if (empty($notifyEmail) && empty($notifyChannel)) {
                return;
            }

            $context = [
                'event'   => $event,
                'message' => $payload,
                'time'    => Time::now('UTC')->toDateTimeString(),
            ];

            log_message('info', 'Contact notification stub triggered', $context);
        } catch (\Throwable $exception) {
            log_message('warning', 'Contact notification hook failed: {message}', [
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
