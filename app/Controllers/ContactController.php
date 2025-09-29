<?php

namespace App\Controllers;

use App\Models\ContactMessageModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class ContactController extends BaseController
{
    private ContactMessageModel $messages;

    public function __construct()
    {
        $this->messages = model(ContactMessageModel::class);
    }

    public function submit(): ResponseInterface
    {
        $request   = $this->request;
        $session   = session();
        $isAjax    = $request->isAJAX();
        $postData  = $request->getPost(['full_name', 'email', 'phone', 'subject', 'message', 'website']);
        $honeypot  = trim((string) ($postData['website'] ?? ''));
        $ipAddress = (string) $request->getIPAddress();

        $payload = [
            'full_name' => trim((string) ($postData['full_name'] ?? '')),
            'email'     => strtolower(trim((string) ($postData['email'] ?? ''))),
            'phone'     => trim((string) ($postData['phone'] ?? '')),
            'subject'   => trim((string) ($postData['subject'] ?? '')),
            'message'   => trim((string) ($postData['message'] ?? '')),
        ];

        if ($honeypot !== '') {
            return $this->respondSuccess($isAjax, 'Pesan Anda berhasil dikirim. Tim kami akan menindaklanjuti secepatnya.');
        }

        $throttler = service('throttler');
        if ($throttler && ! $throttler->check('contact-form:' . md5($ipAddress), 4, 60)) {
            $session->setFlashdata('contact_old', $payload);

            return $this->respondError($isAjax, 'Terlalu banyak percobaan. Mohon tunggu sebelum mengirim ulang.', [], 429, $payload);
        }

        $validation = service('validation');
        $validation->setRules(
            [
                'full_name' => 'required|min_length[3]|max_length[150]',
                'email'     => 'required|valid_email|max_length[150]',
                'phone'     => 'permit_empty|min_length[6]|max_length[30]|regex_match[/^[0-9+().\\s-]{6,}$/]',
                'subject'   => 'required|min_length[3]|max_length[120]',
                'message'   => 'required|min_length[10]|max_length[2000]',
            ],
            [
                'full_name' => [
                    'required'   => 'Nama lengkap wajib diisi.',
                    'min_length' => 'Nama lengkap minimal 3 karakter.',
                    'max_length' => 'Nama lengkap terlalu panjang.',
                ],
                'email' => [
                    'required'    => 'Email wajib diisi.',
                    'valid_email' => 'Format email tidak valid.',
                    'max_length'  => 'Email terlalu panjang.',
                ],
                'phone' => [
                    'min_length'  => 'Nomor telepon minimal 6 karakter.',
                    'max_length'  => 'Nomor telepon terlalu panjang.',
                    'regex_match' => 'Nomor telepon hanya boleh berisi angka dan simbol umum.',
                ],
                'subject' => [
                    'required'   => 'Subjek wajib diisi.',
                    'min_length' => 'Subjek minimal 3 karakter.',
                    'max_length' => 'Subjek terlalu panjang.',
                ],
                'message' => [
                    'required'   => 'Pesan wajib diisi.',
                    'min_length' => 'Pesan minimal 10 karakter.',
                    'max_length' => 'Pesan terlalu panjang.',
                ],
            ]
        );

        if (! $validation->run($payload)) {
            $errors = $validation->getErrors();
            $session->setFlashdata('contact_errors', $errors);
            $session->setFlashdata('contact_error', 'Mohon periksa kembali data yang Anda masukkan.');
            $session->setFlashdata('contact_old', $payload);

            return $this->respondError($isAjax, 'Validasi gagal.', $errors, 422, $payload);
        }

        $sanitizedMessage = $this->sanitizeMessage($payload['message']);
        $userAgent        = $this->captureUserAgent();

        $saveData = [
            'name'        => $payload['full_name'],
            'email'       => $payload['email'],
            'phone'       => $payload['phone'] !== '' ? $payload['phone'] : null,
            'subject'     => $payload['subject'],
            'message'     => $sanitizedMessage,
            'ip_address'  => $ipAddress !== '' ? $ipAddress : null,
            'user_agent'  => $userAgent,
            'status'      => 'new',
        ];

        $insertId = null;

        try {
            $this->messages->insert($saveData);
            $insertId = $this->messages->getInsertID();
        } catch (DatabaseException | Throwable $exception) {
            log_message('error', 'Gagal menyimpan pesan kontak: {message}', ['message' => $exception->getMessage()]);
            $session->setFlashdata('contact_error', 'Terjadi kesalahan pada sistem. Mohon coba lagi beberapa saat.');
            $session->setFlashdata('contact_old', $payload);

            return $this->respondError($isAjax, 'Tidak dapat menyimpan pesan.', [], 500, $payload);
        }

        $storedMessage     = $insertId ? $this->messages->find($insertId) : null;
        $notificationData  = $storedMessage ?? array_merge($saveData, ['id' => $insertId]);

        try {
            if (! function_exists('notify_contact_message')) {
                helper('contact_notifier');
            }

            notify_contact_message('new_submission', $notificationData);
        } catch (Throwable $notifyException) {
            log_message('warning', 'Gagal mengirim notifikasi pesan kontak: {message}', ['message' => $notifyException->getMessage()]);
        }

        $session->remove(['contact_error', 'contact_errors', 'contact_old']);

        return $this->respondSuccess($isAjax, 'Pesan Anda berhasil dikirim. Tim kami akan menindaklanjuti secepatnya.');
    }

    private function sanitizeMessage(string $message): string
    {
        $clean = strip_tags($message);
        $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/u', ' ', $clean);
        $clean = preg_replace("/(\r\n|\r|\n){3,}/", "\n\n", $clean);

        return trim((string) $clean);
    }

    private function captureUserAgent(): ?string
    {
        $agent = $this->request->getUserAgent();

        if (is_object($agent)) {
            $agent = (string) $agent;
        }

        $agent = trim((string) $agent);

        if ($agent === '') {
            return null;
        }

        return mb_strimwidth($agent, 0, 500, '', 'UTF-8');
    }

    private function respondSuccess(bool $isAjax, string $message)
    {
        if ($isAjax) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => $message,
            ]);
        }

        session()->setFlashdata('contact_success', $message);

        return redirect()->to(site_url('kontak'));
    }

    private function respondError(bool $isAjax, string $message, array $errors = [], int $status = 400, array $old = [])
    {
        if ($isAjax) {
            return $this->response
                ->setStatusCode($status)
                ->setJSON([
                    'status'  => 'error',
                    'message' => $message,
                    'errors'  => $errors,
                ]);
        }

        $session = session();
        $session->setFlashdata('contact_error', $message);

        if ($old !== []) {
            $session->setFlashdata('contact_old', $old);
        }

        if ($errors !== []) {
            $session->setFlashdata('contact_errors', $errors);
        }

        return redirect()->back()->withInput();
    }
}
