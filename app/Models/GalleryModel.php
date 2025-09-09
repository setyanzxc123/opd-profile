<?php
namespace App\Models;

use CodeIgniter\Model;

class GalleryModel extends Model
{
    protected $table         = 'galleries';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false; // table uses created_at only (by DB default)
    protected $allowedFields = [
        'title', 'description', 'image_path'
    ];
}

