<?php

namespace App\Http\Controllers;

use App\Repositories\UserRoleRepository;

class UserRoleController extends Controller
{
    public static function repository() {
        return UserRoleRepository::class;
    }
}
