<?php

namespace App\Models;

use CodeIgniter\Model;

class ContactMessageModel extends Model
{
    protected $table          = 'contact_messages';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'ip_address',
        'user_agent',
        'status',
        'admin_note',
        'handled_by',
        'responded_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Count messages with 'new' status (unhandled)
     */
    public function countNewMessages(): int
    {
        return $this->where('status', 'new')->countAllResults();
    }

    /**
     * Count all unhandled messages (new + in_progress)
     */
    public function countUnhandled(): int
    {
        return $this->whereIn('status', ['new', 'in_progress'])->countAllResults();
    }
}
