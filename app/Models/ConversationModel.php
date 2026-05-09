<?php

namespace App\Models;

use CodeIgniter\Model;

class ConversationModel extends Model
{
    protected $table      = 'conversations';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['uuid', 'user_id', 'title', 'persona_id', 'folder_id'];
    protected $beforeInsert  = ['generateUuid'];

    protected function generateUuid(array $data)
    {
        if (!isset($data['data']['uuid'])) {
            $data['data']['uuid'] = bin2hex(random_bytes(16));
        }
        return $data;
    }

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
