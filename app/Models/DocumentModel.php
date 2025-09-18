<?php
namespace App\Models;

use CodeIgniter\Model;

class DocumentModel extends Model
{
    protected $table         = 'documents';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false; // created_at handled by DB default
    protected $allowedFields = [
        'title', 'file_path', 'category', 'year'
    ];
}

