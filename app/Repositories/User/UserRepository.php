<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\BaseRepository;

class UserRepository extends BaseRepository
{
    /**
     * Configure the Model
     **/
    public static function model()
    {
        return User::class;
    }
}
