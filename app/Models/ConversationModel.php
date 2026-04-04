<?php

namespace App\Models;

use CodeIgniter\Model;

class ConversationModel extends Model
{
    protected $table      = 'conversations';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['user_id', 'title'];

    /**
     * Get all conversations for a user, newest first
     */
    public function getForUser(int $userId): array
    {
        return $this->where('user_id', $userId)
                    ->orderBy('updated_at', 'DESC')
                    ->findAll();
    }

    /**
     * Find conversation only if it belongs to this user
     */
    public function findForUser(int $id, int $userId): ?array
    {
        return $this->where('id', $id)->where('user_id', $userId)->first();
    }
}
