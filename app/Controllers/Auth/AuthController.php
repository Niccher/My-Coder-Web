<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;

class AuthController extends BaseController
{
    public function login()
    {
        return view('auth/login');
    }

    public function register()
    {
        return view('auth/register');
    }

    public function forgotPassword()
    {
        return view('auth/forgot_password');
    }

    public function resetPassword()
    {
        return view('auth/reset_password');
    }
}
