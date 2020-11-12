<?php

namespace App\Repositories;

use App\Models\UserLog;
use App\Repositories\BaseRepository;

class UserLogsRepository extends BaseRepository
{
    /**
     * Configure the Model
     **/
    public static function model()
    {
        return UserLog::class;
    }
}
