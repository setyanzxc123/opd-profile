<?php
namespace App\Models;

use CodeIgniter\Model;

class NewsModel extends Model
{
    protected $table         = 'news';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false; // using explicit published_at only
    protected $allowedFields = [
        'title', 'slug', 'content', 'thumbnail', 'published_at', 'author_id'
    ];
}

