<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\PersonaModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

class PersonasController extends BaseController
{
    use ResponseTrait;

    public function index(): ResponseInterface
    {
        $userId = auth()->id();
        $model  = new PersonaModel();
        return $this->respond($model->getForUser($userId));
    }

    public function save(): ResponseInterface
    {
        $userId = auth()->id();
        $body   = $this->request->getJSON(true);
        $id     = (int) ($body['id'] ?? 0);
        $name   = trim($body['name'] ?? '');
        $instructions = trim($body['instructions'] ?? '');
        $isDefault    = (bool) ($body['is_default'] ?? false);

        if ($name === '' || $instructions === '') {
            return $this->fail('Name and instructions are required.', 422);
        }

        $model = new PersonaModel();

        if ($isDefault) {
            $model->clearDefaults($userId);
        }

        $data = [
            'user_id'      => $userId,
            'name'         => $name,
            'instructions' => $instructions,
            'is_default'   => $isDefault,
        ];

        if ($id > 0) {
            $existing = $model->where('id', $id)->where('user_id', $userId)->first();
            if (!$existing) {
                return $this->failNotFound('Persona not found.');
            }
            $model->update($id, $data);
            $newId = $id;
        } else {
            $newId = $model->insert($data, true);
        }

        return $this->respond([
            'message' => 'Persona saved successfully.',
            'id'      => $newId,
        ]);
    }

    public function delete(int $id): ResponseInterface
    {
        $userId = auth()->id();
        $model  = new PersonaModel();
        $persona = $model->where('id', $id)->where('user_id', $userId)->first();

        if (!$persona) {
            return $this->failNotFound('Persona not found.');
        }

        $model->delete($id);
        return $this->respondDeleted(['message' => 'Persona deleted.']);
    }
}
