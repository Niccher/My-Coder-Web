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

    /**
     * GET /api/conversations/{id}
     * Returns a conversation with all messages
     */
    public function show(int $id): ResponseInterface
    {
        $userId    = auth()->id();
        $convModel = new ConversationModel();
        $conv      = $convModel->findForUser($id, $userId);

        if (!$conv) {
            return $this->failNotFound('Conversation not found.');
        }

        $msgModel = new MessageModel();
        $conv['messages'] = $msgModel->getForConversation($id);

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
