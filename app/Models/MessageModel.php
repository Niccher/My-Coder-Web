<?php

namespace App\Models;

use CodeIgniter\Model;

class MessageModel extends Model
{
    protected $table      = 'messages';
    protected $primaryKey = 'id';
    protected $allowedFields = ['conversation_id', 'role', 'model_name', 'content'];
    protected $useTimestamps = true;
    protected $updatedField  = ''; // messages are immutable

    /**
     * Get all messages for a conversation, sorted by time
     */
    public function getForConversation(int $conversationId): array
    {
        return $this->where('conversation_id', $conversationId)
                    ->orderBy('created_at', 'ASC')
                    ->findAll();
    }

    /**
     * Save a user message
     */
    public function saveUserMessage(int $conversationId, string $content): int
    {
        return $this->insert([
            'conversation_id' => $conversationId,
            'role'            => 'user',
            'content'         => $content,
        ], true);
    }

    /**
     * Save an AI model response
     */
    public function saveAiMessage(int $conversationId, string $modelName, string $content): int
    {
        return $this->insert([
            'conversation_id' => $conversationId,
            'role'            => 'ai',
            'model_name'      => $modelName,
            'content'         => $content,
        ], true);
    }
}
