<?php

namespace App\Repositories;

use App\Models\UserStatus;
use App\Repositories\BaseRepository;

class UserStatusRepository extends BaseRepository
{
    /**
     * Configure the Model
     **/
    public static function model()
    {
        return UserStatus::class;
    }
}
