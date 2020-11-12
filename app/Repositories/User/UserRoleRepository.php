<?php

namespace App\Repositories;

use App\Models\UserRole;
use App\Repositories\BaseRepository;

class UserRoleRepository extends BaseRepository
{
    /**
     * Configure the Model
     **/
    public static function model()
    {
        return UserRole::class;
    }
}
