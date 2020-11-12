<?php

namespace App\Repositories;

use App\Models\Referral;
use App\Repositories\BaseRepository;

class ReferralRepository extends BaseRepository
{
    /**
     * Configure the Model
     **/
    public static function model()
    {
        return Referral::class;
    }
}
