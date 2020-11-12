<?php

namespace App\Models;

use App\Models\RestfulModel;

/**
 * 
 * Implements BaseModel.
 * 
 * 
 * The model natively supports following methods which allow
 * advanced usage via appropriate Controller.
 * 
 * @method public function getValidationRules() - validation rules for model creation
 * @method public function getValidationRulesUpdating() - validation rules for model update
 * @method public function getAllowedSorts() - list of attributes for which the sorting is enabled
 * @method public function getAllowedFilters() - list of attributes for which the filtering is enabled
 * @method public function getAllowedFields() - list of attributes for which the selecting is enabled
 * @method public function getAllowedIncludes() - list of method names for which the eager loading is enabled
 * 
 * @var string public $primaryKey - model primary key
 * @var bool public $incrementing - if should use incremental keys
 * @var string protected $keyType - key type (string vs int)
 * @var array public $immutableAttributes - attributes (in addition to primary key) which are not allowed to be updated explicitly
 * @var array public static $itemWith - which relations should model of this entity be returned with
 * @var BaseTransformer public static $transformer - transformer to use for this model
 * 
 */
class BaseModel extends RestfulModel
{
    /**
     * Every model should have a primary ID key, which will be returned to API consumers.
     *
     * @var string ID key
     */
    public $primaryKey = 'uuid';

    /**
     * The attributes that should be hidden for arrays and API output
     *
     * @var array
     */
    protected $hidden = ['laravel_through_key']; 

    /************************************************************
     * Extending Laravel Functions Below
     ***********************************************************/

}
