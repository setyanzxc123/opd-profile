<?php

namespace App\Models;

use CodeIgniter\Model;

class PpidModel extends Model
{
    protected $table      = 'ppid';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'about',           // Tentang PPID (HTML content)
        'vision',          // Visi PPID
        'mission',         // Misi PPID
        'tasks_functions', // Tugas dan Fungsi PPID (HTML content)
        'updated_at',
    ];
    protected $useTimestamps = false;

    /**
     * Get the PPID data (single row)
     */
    public function getPpid(): ?array
    {
        return $this->first();
    }

    /**
     * Update or create PPID data
     */
    public function savePpid(array $data): bool
    {
        $existing = $this->first();
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        if ($existing) {
            return $this->update($existing['id'], $data);
        }
        
        return $this->insert($data) !== false;
    }
}
