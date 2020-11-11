<?php

namespace App\Repositories;

use App\Models\UserLogs;
use App\Repositories\BaseRepository;

class UserLogsRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'operation',
        'scope',
        'description',
        'id_user'
    ];

    /**
     * Return searchable fields
     *
     * @return array
     */
    public function getFieldsSearchable()
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return UserLogs::class;
    }
}
