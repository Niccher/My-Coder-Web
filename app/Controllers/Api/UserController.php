<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Shield\Entities\User;

class UserController extends BaseController
{
    use ResponseTrait;

    /**
     * POST /api/user/profile
     * Updates the current user's profile data (username, email, avatar).
     */
    public function updateProfile(): ResponseInterface
    {
        $user = auth()->user();
        if (!$user) {
            return $this->failUnauthorized();
        }

        $username = $this->request->getPost('username');
        $bio      = $this->request->getPost('bio');
        $avatar   = $this->request->getFile('avatar');

        $rules = [
            'username' => 'required|min_length[3]|max_length[30]',
            'bio'      => 'permit_empty|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Update username and bio
        $user->username = $username;
        $user->bio      = $bio;

        // Handle Avatar Upload
        if ($avatar && $avatar->isValid() && !$avatar->hasMoved()) {
            $newName = $user->id . '_' . $avatar->getRandomName();
            
            // Create directory if not exists
            $uploadPath = FCPATH . 'uploads/avatars/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            // Remove old avatar if exists
            if ($user->avatar && file_exists($uploadPath . $user->avatar)) {
                unlink($uploadPath . $user->avatar);
            }

            $avatar->move($uploadPath, $newName);
            $user->avatar = $newName;
        }

        $users = auth()->getProvider();
        $users->save($user);

        return $this->respond([
            'message' => 'Profile updated successfully.',
            'user'    => [
                'username' => $user->username,
                'bio'      => $user->bio,
                'avatar'   => $user->avatar ? base_url('uploads/avatars/' . $user->avatar) : null
            ]
        ]);
    }
}
