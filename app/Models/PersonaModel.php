<?php

namespace App\Models;

use CodeIgniter\Model;

class PersonaModel extends Model
{
    protected $table      = 'personas';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['user_id', 'name', 'instructions', 'is_default'];

    public function getForUser(int $userId): array
    {
        return $this->where('user_id', $userId)->orderBy('created_at', 'DESC')->findAll();
    }

    public function getDefault(int $userId): ?array
    {
        return $this->where('user_id', $userId)->where('is_default', true)->first();
    }

    public function clearDefaults(int $userId): void
    {
        $this->where('user_id', $userId)->set(['is_default' => false])->update();
    }
}
