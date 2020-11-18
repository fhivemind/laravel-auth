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
 * @method public function getWithRelationships() - list of relationships that the entity will be returned with
 * @method public function getSortAttributes() - list of attributes for which the sorting is enabled
 * @method public function getFilterAttributes() - list of attributes for which the filtering is enabled
 * @method public function getSelectAttributes() - list of attributes for which the selecting is enabled
 * @method public function getIncludeRelationships() - list of relationships for which the eager loading is enabled
 * @method public function getAppendAttributes() - list of custom allowed attributes that are going to be read from model methods
 * 
 * @var string public $primaryKey - model primary key
 * @var bool public $incrementing - if should use incremental keys
 * @var string protected $keyType - key type (string vs int)
 * @var array public $immutable - attributes (in addition to primary key) which are not allowed to be updated explicitly
 * @var array protected $appends - adds custom resources to model
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
    public $primaryKey = 'id';

    /**
     * The attributes that should be hidden for arrays and API output
     *
     * @var array
     */
    protected $hidden = ['laravel_through_key']; 
}
