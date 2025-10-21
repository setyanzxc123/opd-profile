<?php
namespace App\Models;

use CodeIgniter\Model;

class OpdProfileModel extends Model
{
    protected $table      = 'opd_profile';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'name',
        'description',
        'vision',
        'mission',
        'address',
        'latitude',
        'longitude',
        'map_zoom',
        'map_display',
        'phone',
        'email',
        'logo_public_path',
        'logo_admin_path',
        'theme_settings',
    ];
    protected $useTimestamps = false; // schema has no created_at/updated_at
}
