<?php
namespace App\Models;

use CodeIgniter\Model;

class ServiceModel extends Model
{
    protected $table         = 'services';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'title',
        'slug',
        'description',
        'requirements',
        'fees',
        'processing_time',
        'is_active',
        'sort_order',
    ];
}
