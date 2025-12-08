<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PpidModel;

class Ppid extends BaseController
{
    protected PpidModel $model;

    public function __construct()
    {
        $this->model = new PpidModel();
    }

    /**
     * Redirect to edit form
     */
    public function index(): string
    {
        return $this->edit();
    }

    /**
     * Display the PPID edit form
     */
    public function edit(): string
    {
        $ppid = $this->model->getPpid();

        // If no PPID data exists, create empty structure
        if (!$ppid) {
            $ppid = [
                'id'              => null,
                'about'           => '',
                'vision'          => '',
                'mission'         => '',
                'tasks_functions' => '',
                'updated_at'      => null,
            ];
        }

        return view('admin/ppid/edit', [
            'title'      => 'Kelola PPID',
            'ppid'       => $ppid,
            'validation' => session('validation'),
        ]);
    }

    /**
     * Update PPID data
     */
    public function update()
    {
        $rules = [
            'about'           => 'permit_empty|string',
            'vision'          => 'permit_empty|string',
            'mission'         => 'permit_empty|string',
            'tasks_functions' => 'permit_empty|string',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('validation', $this->validator)
                ->with('error', 'Validasi gagal, periksa kembali input Anda.');
        }

        $data = [
            'about'           => $this->request->getPost('about'),
            'vision'          => $this->request->getPost('vision'),
            'mission'         => $this->request->getPost('mission'),
            'tasks_functions' => $this->request->getPost('tasks_functions'),
        ];

        if ($this->model->savePpid($data)) {
            // Log activity
            $logModel = model('App\Models\ActivityLogModel');
            $logModel->insert([
                'user_id'    => session('admin_id'),
                'action'     => 'update',
                'module'     => 'ppid',
                'record_id'  => $this->model->getPpid()['id'] ?? null,
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent()->getAgentString(),
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            return redirect()->to(site_url('admin/ppid'))
                ->with('message', 'Data PPID berhasil diperbarui.');
        }

        return redirect()->back()
            ->withInput()
            ->with('error', 'Gagal menyimpan data PPID.');
    }
}
