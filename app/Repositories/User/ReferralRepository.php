<?php

namespace App\Repositories;

use App\Models\Referral;
use App\Repositories\BaseRepository;

class ReferralRepository extends BaseRepository
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
            'user_id',
            'referral_user_id'
        ];
    }

    /**
     * Configure the Model
     **/
    public static function model()
    {
        return Referral::class;
    }
}
