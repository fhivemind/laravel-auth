<?php

namespace App\Http\Controllers;

use App\Repositories\ReferralRepository;

class ReferralController extends Controller
{
    public static function repository() {
        return ReferralRepository::class;
    }
}
