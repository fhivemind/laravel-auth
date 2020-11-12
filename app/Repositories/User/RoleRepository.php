<?php

namespace App\Repositories;

use App\Models\Role;
use App\Repositories\BaseRepository;

class RoleRepository extends BaseRepository
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
            'name',
            'description',
        ];
    }

    /**
     * Configure the Model
     **/
    public static function model()
    {
        return Role::class;
    }
}
