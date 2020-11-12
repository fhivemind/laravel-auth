<?php

namespace App\Http\Controllers;

use App\Models\UserLog;
use App\Repositories\UserLogsRepository;

class UserLogsController extends Controller
{
    public static function repository() {
        return UserLogsRepository::class;
    }
}
