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
            $conv = $convModel->findForUser($id, $userId);
        } else {
            $conv = $convModel->where('uuid', $id)->where('user_id', $userId)->first();
        }

        if (!$conv) {
            return $this->failNotFound('Conversation not found.');
        }

        $msgModel = new MessageModel();
        $conv['messages'] = $msgModel->getForConversation($conv['id']);

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
}
