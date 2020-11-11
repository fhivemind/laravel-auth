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
     *
     * @return array Set validation rule for model
     */
    public static $rules = [];

    /**
     * Return the validation rules for this model
     *
     * @return array Rules
     */
    public function getValidationRules()
    {
        return static::$rules;
    }
}
