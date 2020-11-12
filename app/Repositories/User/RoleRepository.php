<?php

namespace App\Repositories;

use App\Models\Role;
use App\Repositories\BaseRepository;

class RoleRepository extends BaseRepository
{
    /**
     * Configure the Model
     **/
    public static function model()
    {
        return Role::class;
    }
}
