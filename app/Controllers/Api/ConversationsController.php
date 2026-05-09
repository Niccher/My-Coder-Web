<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ConversationModel;
use App\Models\MessageModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

class ConversationsController extends BaseController
{
    use ResponseTrait;

    /**
     * GET /api/conversations
     * Returns all conversations for the current user (for sidebar listing)
     */
    public function index(): ResponseInterface
    {
        $userId = auth()->id();
        $model  = new ConversationModel();
        return $this->respond($model->getForUser($userId));
    }

    public function listPaginated(): ResponseInterface
    {
        $userId = auth()->id();
        $page   = (int) $this->request->getGet('page') ?: 1;
        $search = (string) $this->request->getGet('search');
        $limit  = 10;
        $offset = ($page - 1) * $limit;

        $db = \Config\Database::connect();
        $builder = $db->table('conversations');
        $builder->select('conversations.*, GROUP_CONCAT(DISTINCT messages.model_name) as models')
                ->join('messages', 'messages.conversation_id = conversations.id AND messages.role = "ai"', 'left')
                ->where('conversations.user_id', $userId);
        
        if (!empty($search)) {
            $builder->like('conversations.title', $search);
        }

        $builder->groupBy('conversations.id');
        $total = $builder->countAllResults(false);

        $builder->orderBy('conversations.updated_at', 'DESC');
        $builder->limit($limit, $offset);
        $results = $builder->get()->getResultArray();

        return $this->respond([
            'data'  => $results,
            'total' => $total,
            'page'  => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit),
        ]);
    }

    /**
     * GET /api/conversations/{id}
     * Returns a conversation with all messages
     */
    public function show($id): ResponseInterface
    {
        $userId    = auth()->id();
        $convModel = new ConversationModel();
        
        if (is_numeric($id)) {
            $conv = $convModel->select('conversations.*, personas.name as persona_name')
                             ->join('personas', 'personas.id = conversations.persona_id', 'left')
                             ->where('conversations.id', $id)
                             ->where('conversations.user_id', $userId)
                             ->first();
        } else {
            $conv = $convModel->select('conversations.*, personas.name as persona_name')
                             ->join('personas', 'personas.id = conversations.persona_id', 'left')
                             ->where('conversations.uuid', $id)
                             ->where('conversations.user_id', $userId)
                             ->first();
        }

        if (!$conv) {
            return $this->failNotFound('Conversation not found.');
        }

        $msgModel = new MessageModel();
        $conv['messages'] = $msgModel->getForConversation((int)$conv['id']);

        return $this->respond($conv);
    }

    /**
     * DELETE /api/conversations/{id}
     */
    public function delete(int $id): ResponseInterface
    {
        $userId    = auth()->id();
        $convModel = new ConversationModel();
        $conv      = $convModel->findForUser($id, $userId);

        if (!$conv) {
            return $this->failNotFound('Conversation not found.');
        }

        $msgModel = new MessageModel();
        $msgModel->where('conversation_id', $id)->delete();
        $convModel->delete($id);

        return $this->respondDeleted(['message' => 'Conversation deleted.']);
    }

    /**
     * POST /api/conversations/{id}/branch
     * Body: { "message_id": int }
     * Creates a new conversation forked from the given message point.
     */
    public function branch(int $id): ResponseInterface
    {
        $userId    = auth()->id();
        $convModel = new ConversationModel();
        $conv      = $convModel->findForUser($id, $userId);

        if (!$conv) {
            return $this->failNotFound('Conversation not found.');
        }

        $body      = $this->request->getJSON(true);
        $messageId = (int) ($body['message_id'] ?? 0);

        if ($messageId <= 0) {
            return $this->fail('message_id is required.', 422);
        }

        $msgModel = new MessageModel();

        // Find the target message to get its created_at timestamp
        $targetMsg = $msgModel->find($messageId);
        if (!$targetMsg || (int) $targetMsg['conversation_id'] !== $id) {
            return $this->failNotFound('Message not found in this conversation.');
        }

        // Fetch all messages up to and including the target message
        $messagesToCopy = $msgModel
            ->where('conversation_id', $id)
            ->where('created_at <=', $targetMsg['created_at'])
            ->orderBy('created_at', 'ASC')
            ->findAll();

        // Create the new branched conversation
        $branchTitle  = mb_substr($conv['title'], 0, 50) . ' (Branch)';
        $newConvId    = $convModel->insert(['user_id' => $userId, 'title' => $branchTitle], true);

        // Bulk-insert copied messages
        foreach ($messagesToCopy as $msg) {
            $msgModel->insert([
                'conversation_id' => $newConvId,
                'role'            => $msg['role'],
                'model_name'      => $msg['model_name'] ?? null,
                'content'         => $msg['content'],
            ]);
        }

        // Retrieve the new conversation's UUID
        $newConv = $convModel->find($newConvId);

        return $this->respond([
            'conversation_id' => $newConvId,
            'uuid'            => $newConv['uuid'] ?? null,
            'title'           => $branchTitle,
            'message_count'   => count($messagesToCopy),
        ], 201);
    }

    /**
     * PATCH /api/conversations/{id}/folder
     */
    public function updateFolder(int $id): ResponseInterface
    {
        $userId    = auth()->id();
        $convModel = new ConversationModel();
        $conv      = $convModel->findForUser($id, $userId);

        if (!$conv) {
            return $this->failNotFound('Conversation not found.');
        }

        $body     = $this->request->getJSON(true);
        $folderId = isset($body['folder_id']) ? (int)$body['folder_id'] : null;

        $db = \Config\Database::connect();
        $db->table('conversations')
           ->where('id', $id)
           ->where('user_id', $userId)
           ->update(['folder_id' => $folderId]);

        return $this->respond(['message' => 'Folder updated successfully']);
    }
}
