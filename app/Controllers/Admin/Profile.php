<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\OpdProfileModel;

class Profile extends BaseController
{
    // Purpose: display edit form for the single OPD profile
    public function index()
    {
        return $this->edit();
    }

    public function edit()
    {
        $model = new OpdProfileModel();
        // we keep a single-row profile; fetch first or create a placeholder
        $profile = $model->orderBy('id', 'ASC')->first();
        if (!$profile) {
            $model->insert([
                'name' => '',
                'description' => null,
                'vision' => null,
                'mission' => null,
                'address' => null,
                'phone' => null,
                'email' => null,
            ]);
            $profile = $model->orderBy('id', 'ASC')->first();
        }

        return view('admin/profile/edit', [
            'title' => 'Profil OPD',
            'profile' => $profile,
            'validation' => \Config\Services::validation(),
        ]);
    }

    public function update()
    {
        $rules = [
            'name' => 'required|min_length[3]|max_length[150]',
            'email' => 'permit_empty|valid_email|max_length[100]',
            'phone' => 'permit_empty|max_length[20]',
            'description' => 'permit_empty',
            'vision' => 'permit_empty',
            'mission' => 'permit_empty',
            'address' => 'permit_empty',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error','Please correct the errors below.');
        }

        $model = new OpdProfileModel();
        $id = (int) $this->request->getPost('id');
        $data = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'vision' => $this->request->getPost('vision'),
            'mission' => $this->request->getPost('mission'),
            'address' => $this->request->getPost('address'),
            'phone' => $this->request->getPost('phone'),
            'email' => $this->request->getPost('email'),
        ];

        if ($id > 0) {
            $model->update($id, $data);
        } else {
            $model->insert($data);
        }

        return redirect()->to(site_url('admin/profile'))
            ->with('message', 'Profile has been saved.');
    }
}

