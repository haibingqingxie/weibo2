<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UsersController extends Controller
{
    // 注册用户
    public function create()
    {
        return view('users.create');
    }

    // 展示用户
    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }
}
