<?php

namespace App\Controllers;

class Chat extends BaseController
{
    public function index($uuid = null)
    {
        return view('chat/index', ['chatUuid' => $uuid]);
    }
}
