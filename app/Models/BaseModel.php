<?php

namespace App\Models;

use App\Models\RestfulModel;

/**
 * 
 * Implements RestfulModel.
 * 
 * 
 * The model natively supports following methods which allow
 * advanced usage via appropriate Controller.
 * 
 * @method public function getValidationRules() - validation rules for model creation
 * @method public function getValidationRulesUpdating() - validation rules for model update
 * @method public function getWith() - list of eager loading relations that model supports
 * @method public function getQuerySorts() - list of attributes for which the sorting via query is supported
 * @method public function getQueryFilters() - list of attributes for which the filtering via query is supported
 * @method public function getQueryFields() - list of attributes for which the selecting via query is supported
 * @method public function getQueryIncludes() - list of relations for which the eager loading via query is supported
 * @method public function getQueryAppends() - list of supported custom query attributes
 * 
 * @method public function getAuthorizedEditableAttributes() - list of attributes current user can mass edit (fillables)
 * @method public function getAuthorizedWith() - list of eager loading relations allowed for current user
 * @method public function getAuthorizedQuerySorts() - list of sorting attributes allowed for current user
 * @method public function getAuthorizedQueryFilters() - list of filtering attributes allowed for current user
 * @method public function getAuthorizedQueryFields() - list of selecting attributes allowed for current user
 * @method public function getAuthorizedQueryIncludes() - list of eager loading relations allowed for current user
 * @method public function getAuthorizedQueryAppends() - list of custom query attributes allowed for current user
 * 
 * @var array public $immutable - attributes (in addition to primary key) which are not allowed to be explicitly updated
 * @var BaseTransformer public static $transformer - transformer to use for the model
 * 
 * Please note that it is possible to write Policy authorization methods for hidden and mass assignable attributes, which
 * should start with prefixes 'view' and 'edit' respectively. The logic is defined under @var AuthorizedAttributes.
 * 
 * It is also possible to write Policy authorization methods for query attributes, as defined in @var AuthorizedQuery.
 * 
 */
class BaseModel extends RestfulModel
{
    /**
     * The attributes that should be hidden for arrays and API output
     *
     * @var array
     */
    protected $hidden = ['laravel_through_key']; 
}
