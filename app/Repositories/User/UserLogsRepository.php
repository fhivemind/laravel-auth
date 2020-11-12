<?php

namespace App\Repositories;

use App\Models\UserLog;
use App\Repositories\BaseRepository;

class UserLogsRepository extends BaseRepository
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
            'operation',
            'scope',
            'description',
            'id_user'
        ];
    }

    /**
     * Configure the Model
     **/
    public static function model()
    {
        return UserLog::class;
    }
}
