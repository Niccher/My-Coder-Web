<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use Config\Database;

class FoldersController extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        $userId = auth()->id();
        $db = Database::connect();
        $folders = $db->table('folders')
                      ->where('user_id', $userId)
                      ->orderBy('name', 'ASC')
                      ->get()
                      ->getResultArray();

        return $this->respond($folders);
    }

    public function save()
    {
        $userId = auth()->id();
        $name = $this->request->getJSON(true)['name'] ?? '';

        if (empty($name)) {
            return $this->fail('Folder name is required');
        }

        $db = Database::connect();
        $data = [
            'user_id' => $userId,
            'name'    => $name,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $db->table('folders')->insert($data);
        return $this->respondCreated(['id' => $db->insertID(), 'name' => $name]);
    }

    public function delete($id)
    {
        $userId = auth()->id();
        $db = Database::connect();

        // Check ownership
        $folder = $db->table('folders')->where('id', $id)->where('user_id', $userId)->get()->getRow();
        if (!$folder) {
            return $this->failNotFound('Folder not found');
        }

        // Unlink conversations
        $db->table('conversations')->where('folder_id', $id)->update(['folder_id' => null]);
        
        // Delete folder
        $db->table('folders')->where('id', $id)->delete();

        return $this->respondDeleted(['id' => $id]);
    }
}
