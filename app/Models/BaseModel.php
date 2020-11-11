<?php

namespace App\Models;

use App\Models\RestfulModel;

class BaseModel extends RestfulModel
{
    /**
     * Every model should have a primary ID key, which will be returned to API consumers.
     *
     * @var string ID key
     */
    public $primaryKey = 'id';

    /**
     * @var bool Set to false for UUID keys
     */
    public $incrementing = true;

    /**
     * @var string Set to string for UUID keys
     */
    protected $keyType = 'int';

    /**
     * The attributes that should be hidden for arrays and API output
     *
     * @var array
     */
    protected $hidden = ['laravel_through_key']; 
}
