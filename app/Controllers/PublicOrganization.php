<?php

namespace App\Controllers;

use App\Models\OpdProfileModel;

class PublicOrganization extends BaseController
{
    public function index(): string
    {
        $profileModel = new OpdProfileModel();
        $profile = $profileModel->orderBy('id', 'ASC')->first();

        if (! $profile) {
            $profile = [
                'name' => 'OPD',
                'org_structure_image' => null,
                'org_structure_alt_text' => null,
                'org_structure_updated_at' => null,
            ];
        }

        return view('public/organization/index', [
            'title' => 'Struktur Organisasi',
            'profile' => $profile,
        ]);
    }
}
