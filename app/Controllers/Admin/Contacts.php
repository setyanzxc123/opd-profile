<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ContactMessageModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class Contacts extends BaseController
{
    private ContactMessageModel $messages;

    public function __construct()
    {
        helper('auth');
        $this->messages = model(ContactMessageModel::class);
    }

    public function index(): string
    {
        $request      = $this->request;
        $statusFilter = trim((string) $request->getGet('status'));
        $searchTerm   = trim((string) $request->getGet('q'));
        $perPage      = (int) $request->getGet('per_page');

        $perPageOptions = [10, 15, 25, 50];
        if (! in_array($perPage, $perPageOptions, true)) {
            $perPage = 15;
        }

        $statusLabels  = $this->statusOptions();
        $statusFilters = array_merge(['all' => 'Semua'], $statusLabels);

        if ($statusFilter !== '' && $statusFilter !== 'all' && ! array_key_exists($statusFilter, $statusLabels)) {
            $statusFilter = 'all';
        }

        $query = $this->messages
            ->select($this->selectHandlerColumns())
            ->join('users AS handler', 'handler.id = contact_messages.handled_by', 'left')
            ->orderBy('contact_messages.created_at', 'DESC');

        if ($statusFilter !== '' && $statusFilter !== 'all') {
            $query->where('contact_messages.status', $statusFilter);
        }

        if ($searchTerm !== '') {
            $query->groupStart()
                ->like('contact_messages.name', $searchTerm)
                ->orLike('contact_messages.email', $searchTerm)
                ->orLike('contact_messages.phone', $searchTerm)
                ->orLike('contact_messages.subject', $searchTerm)
                ->orLike('contact_messages.ip_address', $searchTerm)
                ->groupEnd();
        }

        $messages = $query->paginate($perPage, 'contacts');
        $pager    = $this->messages->pager;

        $countsRaw = $this->messages
            ->select('status, COUNT(*) AS total')
            ->groupBy('status')
            ->findAll();

        $statusCounts = array_fill_keys(array_keys($statusLabels), 0);
        foreach ($countsRaw as $row) {
            $status = $row['status'];
            if (isset($statusCounts[$status])) {
                $statusCounts[$status] = (int) $row['total'];
            }
        }
        $statusCounts['all'] = array_sum($statusCounts);

        return view('admin/contacts/index', [
            'title'          => 'Pesan Kontak',
            'messages'       => $messages,
            'pager'          => $pager,
            'statusFilters'  => $statusFilters,
            'statusLabels'   => $statusLabels,
            'statusCounts'   => $statusCounts,
            'perPageOptions' => $perPageOptions,
            'filters'        => [
                'status'   => $statusFilter === '' ? 'all' : $statusFilter,
                'q'        => $searchTerm,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function show(int $id): string
    {
        $message = $this->messages
            ->select($this->selectHandlerColumns(true))
            ->join('users AS handler', 'handler.id = contact_messages.handled_by', 'left')
            ->find($id);

        if (! $message) {
            throw PageNotFoundException::forPageNotFound('Pesan kontak tidak ditemukan');
        }

        return view('admin/contacts/show', [
            'title'        => 'Detail Pesan Kontak',
            'message'      => $message,
            'statusLabels' => $this->statusOptions(),
        ]);
    }

    public function updateStatus(int $id): ResponseInterface
    {
        $message = $this->messages->find($id);
        if (! $message) {
            throw PageNotFoundException::forPageNotFound('Pesan kontak tidak ditemukan');
        }

        $rules = [
            'status'     => 'required|in_list[new,in_progress,closed]',
            'admin_note' => 'permit_empty|max_length[5000]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $status    = (string) $this->request->getPost('status');
        $adminNote = trim((string) $this->request->getPost('admin_note'));
        $userId    = $this->currentUserId();

        $updateData = [
            'status'     => $status,
            'admin_note' => $adminNote !== '' ? $adminNote : null,
            'handled_by' => $userId,
        ];

        if ($status === 'closed') {
            $updateData['responded_at'] = date('Y-m-d H:i:s');
        } else {
            $updateData['responded_at'] = null;
        }

        try {
            $this->messages->update($id, $updateData);
        } catch (DatabaseException | Throwable $exception) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui status pesan.');
        }

        helper('activity');
        $statusLabel = $this->statusOptions()[$status] ?? $status;
        log_activity('contacts.update_status', sprintf('Memperbarui status pesan kontak #%d menjadi %s', $id, $statusLabel));

        $updatedMessage = $this->messages->find($id) ?? array_merge($message, $updateData);

        return redirect()->to(site_url('admin/contacts/' . $id))
            ->with('message', 'Status pesan berhasil diperbarui.');
    }

    public function bulkUpdateStatus(): ResponseInterface
    {
        $status = (string) $this->request->getPost('status');
        $ids    = array_filter(array_map('intval', (array) $this->request->getPost('ids')));
        $redirectTo = $this->request->getPost('redirect_to') ?: site_url('admin/contacts');

        if ($status === '' || ! array_key_exists($status, $this->statusOptions())) {
            return redirect()->to($redirectTo)->with('error', 'Status baru tidak valid.');
        }

        if ($ids === []) {
            return redirect()->to($redirectTo)->with('error', 'Pilih minimal satu pesan untuk diperbarui.');
        }

        $updateData = [
            'status'     => $status,
            'handled_by' => $this->currentUserId(),
        ];

        if ($status === 'closed') {
            $updateData['responded_at'] = date('Y-m-d H:i:s');
        } else {
            $updateData['responded_at'] = null;
        }

        try {
            $this->messages->update($ids, $updateData);
        } catch (DatabaseException | Throwable $exception) {
            return redirect()->to($redirectTo)->with('error', 'Gagal memperbarui status massal.');
        }

        helper('activity');
        $statusLabel = $this->statusOptions()[$status] ?? $status;
        log_activity('contacts.bulk_update_status', sprintf('Memperbarui status %d pesan kontak menjadi %s', count($ids), $statusLabel));

        return redirect()->to($redirectTo)->with('message', 'Status pesan terpilih berhasil diperbarui.');
    }


    private function statusOptions(): array
    {
        return [
            'new'         => 'Baru',
            'in_progress' => 'Diproses',
            'closed'      => 'Selesai',
        ];
    }

    private function currentUserId(): ?int
    {
        $auth = auth('session');
        if (! $auth->loggedIn()) {
            return null;
        }

        return (int) ($auth->user()->id ?? 0) ?: null;
    }

    private function selectHandlerColumns(bool $includeEmail = false): string
    {
        $db = $this->messages->db;
        $columns = ['contact_messages.*'];

        if ($db->fieldExists('name', 'users')) {
            $columns[] = 'handler.name AS handler_name';
        } else {
            $columns[] = 'handler.username AS handler_name';
        }

        if ($includeEmail && $db->fieldExists('email', 'users')) {
            $columns[] = 'handler.email AS handler_email';
        }

        return implode(', ', $columns);
    }
}
