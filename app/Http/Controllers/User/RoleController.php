<?php

namespace App\Http\Controllers;

use App\Repositories\RoleRepository;

class RoleController extends Controller
{
    public static function repository() {
        return RoleRepository::class;
    }
}
