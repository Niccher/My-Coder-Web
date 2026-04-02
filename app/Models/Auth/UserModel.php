<?php

namespace App\Models\Auth;

use CodeIgniter\Model;

class UserModel extends Model
{
    // Skeleton User Model for future Shield Auth Integration
    protected $table = 'users';
    protected $primaryKey = 'id';
    
    protected $allowedFields = [
        'username', 'email', 'password_hash'
    ];
}
