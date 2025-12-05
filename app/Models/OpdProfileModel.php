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
        'name_line2',
        'hide_brand_text',
        'description',
        'vision',
        'mission',
        'greeting',
        'tasks_functions',
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
        'org_structure_image',
        'org_structure_alt_text',
        'org_structure_updated_at',
        'social_facebook',
        'social_facebook_active',
        'social_instagram',
        'social_instagram_active',
        'social_twitter',
        'social_twitter_active',
        'social_youtube',
        'social_youtube_active',
        'social_tiktok',
        'social_tiktok_active',
        'operational_hours',
        'operational_notes',
    ];
    protected $useTimestamps = false; // schema has no created_at/updated_at
}
