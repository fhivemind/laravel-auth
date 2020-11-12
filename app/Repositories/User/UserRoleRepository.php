<?php

namespace App\Repositories;

use App\Models\UserRole;
use App\Repositories\BaseRepository;

class UserRoleRepository extends BaseRepository
{
    /**
     * Return searchable fields
     *
     * @return array
     */
    public static function getFieldsSearchable()
    {
        return [
            'id',
            'active',
            'id_role',
            'id_user'
        ];
    }

    /**
     * Configure the Model
     **/
    public static function model()
    {
        return UserRole::class;
    }
}
