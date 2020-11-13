<?php

namespace App\Http\Controllers;

use App\Repositories\UserStatusRepository;

class UserStatusController extends Controller
{
    public static function repository() {
        return UserStatusRepository::class;
    }
}
