<?php

namespace App\Http\Controllers;

use App\Repositories\UserRepository;

class UserController extends Controller
{
    public static function repository() {
        return UserRepository::class;
    }
}
