<?php

namespace App\Models\Traits;

use App\Enums\SpatieQuery;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\AllowedFilter;
use Gate;
use stdClass;

trait AuthorizedQuery
{
    /************************************************************
     * Public interface
     ***********************************************************/
    /**
     * List of method prefix name for the attribute query ability in the model policy.
     * 
     * Naming:  %s_POLICY_PREFIX
     * 
     */
    public static $WITH_POLICY_PREFIX    = "with";
    public static $FIELD_POLICY_PREFIX   = "field";
    public static $FILTER_POLICY_PREFIX  = "filter";
    public static $SORT_POLICY_PREFIX    = "sort";
    public static $INCLUDE_POLICY_PREFIX = "include";
    public static $APPEND_POLICY_PREFIX  = "append";

    /**
     * List of query method identifiers. This should never be changed!
     * 
     * Naming: %s_METHOD
     * 
     */
    protected static $WITH_METHOD    = "with";
    protected static $FIELD_METHOD   = "fields";
    protected static $FILTER_METHOD  = "filters";
    protected static $SORT_METHOD    = "sorts";
    protected static $INCLUDE_METHOD = "includes";
    protected static $APPEND_METHOD  = "appends";

    /**
     * List of supported relationships that the entity can be returned with.
     * 
     * Its counterpart `getAuthorizedQueryWith()` validates which of these 
     * relationships are visible for the current user.
     *
     * @return array
     */
    public function getWith() {
        return [];
    }

    /**
     * List of attributes for which sorting is supported through queries.
     * 
     * Its counterpart `getAuthorizedQuerySorts()` validates which of these 
     * attributes are visible for the current user.
     * 
     * Example: ?sort=-name
     * 
     * @return array
     */
    public function getQuerySorts() {
        return [];
    }

    /**
     * List of attributes for which filtering is supported through queries.
     * 
     * Its counterpart `getAuthorizedQueryFilters()` validates which of these 
     * attributes are visible for the current user.
     *
     * Example: ?filter[name]=john&filter[email]=gmail
     * 
     * @return array
     */
    public function getQueryFilters() {
        return [];
    }

    /**
     * List of attributes for which selecting is supported through queries.
     * 
     * Its counterpart `getAuthorizedQueryFields()` validates which of these 
     * attributes are visible for the current user.
     *
     * Example: ?fields[users]=id,name
     * 
     * @return array
     */
    public function getQueryFields() {
        return [];
    }

    /**
     * List of relations for which relationship (eager) loading is supported through queries.
     * 
     * Its counterpart `getAuthorizedQueryIncludes()` validates which of these 
     * attributes are visible for the current user.
     *
     * Example: ?include=posts
     * 
     * @return array
     */
    public function getQueryIncludes() {
        return [];
    }

    /**
     * List of custom attributes for which appending is supported through queries.
     * 
     * Its counterpart `getAuthorizedQueryAppends()` validates which of these 
     * attributes are visible for the current user.
     * 
     * Implementation: public function getFullnameAttribute() { return "{$this->firstname} {$this->lastname"; }
     * Example: ?append=fullname
     * 
     * @return array
     */
    public function getQueryAppends() {
        return [];
    }
    
    /************************************************************
     * Authorized queries from policy
     * TODO: refactor to support relation related activities:
     * TODO: filtering, selecting, eager loading
     * 
     * TODO: note that DOT notation is used to differ from 
     * TODO: model and relation requests
     ***********************************************************/
    /**
     * Get list of attributes for which the sorting is allowed for current user.
     * 
     * @var array
     */
    public function getAuthorizedQuerySorts()
    {
        return [$this->primaryKey] + $this->getQueryableAttributesFor(
            static::$SORT_METHOD, 
            $this->getQuerySorts()
        );
    }

    /**
     * Get list of attributes for which the filtering is allowed for current user.
     *
     * TODO: Support relation filtering, e.g. /user?includes=logs&filter[logs.scope]=ADMIN
     * 
     * @return array
     */
    public function getAuthorizedQueryFilters()
    {
        return [AllowedFilter::exact($this->primaryKey)] + $this->getQueryableAttributesFor(
            static::$FILTER_METHOD, 
            $this->getQueryFilters()
        );
    }

    /**
     * Get list of attributes for which the selecting is allowed for current user.
     *
     * TODO: Support relation selection, e.g. user/includes=logs&fields[logs]=scope
     * 
     * @return array
     */
    public function getAuthorizedQueryFields()
    {
        // // find relation object policy and merge with query
        // foreach($this->getAuthorizedQueryIncludes() as $relation) {
        //     $related = get_class($this->{Str::camel($relation)}()->getRelated());
        //     var_dump($related);
        // }

        return [$this->primaryKey] + $this->getQueryableAttributesFor(
            static::$FIELD_METHOD, 
            $this->getQueryFields()
        );
    }

    /**
     * Get list of relationships for which eager loading through queries is allowed for current user.
     *
     * @return array
     */
    public function getAuthorizedQueryIncludes()
    {
        return $this->getQueryableAttributesFor(
            static::$INCLUDE_METHOD, 
            $this->getQueryIncludes()
        );
    }

    /**
     * Get list of custom attributes which are allowed for current user. 
     *
     * @return array
     */
    public function getAuthorizedQueryAppends()
    {
        return $this->getQueryableAttributesFor(
            static::$APPEND_METHOD, 
            $this->getQueryAppends()
        );
    }

    /**
     * Get list of eager loading relationships which are allowed for current user.
     * 
     * TODO: Drop relations when user specifically requesting via fields, e.g. /user?fields[user]=id,name
     * 
     * @return array
     */
    public function getAuthorizedWith()
    {
        return $this->getQueryableAttributesFor(
            static::$WITH_METHOD, 
            $this->getWith()
        );
    }

    /************************************************************
     * Authorization policy
     ***********************************************************/
     /**
     * Defines a list of query attributes to method mappings
     * used to find proper method in model policy.
     * 
     * This maps the type of query to policy method prefixes.
     * 
     * @return array
     */
    protected static function getPolicyMappings() {
        return [
            static::$WITH_METHOD    => static::$WITH_POLICY_PREFIX,
            static::$SORT_METHOD    => static::$SORT_POLICY_PREFIX,
            static::$FILTER_METHOD  => static::$FILTER_POLICY_PREFIX,
            static::$FIELD_METHOD   => static::$FIELD_POLICY_PREFIX,
            static::$INCLUDE_METHOD => static::$INCLUDE_POLICY_PREFIX,
            static::$APPEND_METHOD  => static::$APPEND_POLICY_PREFIX,
        ];
    }

     /**
     * Returns a list of all query key method names.
     * Keys represent @var QueryBuilderRequest public function names,
     * and values their matching base AuthorizedQuery method identifiers.
     * 
     * This maps SpatieQuery to ATTR parts defined via
     * `getQueryATTR` or `getAuthorizedQueryATTR`.
     * 
     * @return array
     */
    public static function getSpatieIdentifierMappings() {
        return [
            SpatieQuery::Sort    => static::$SORT_METHOD,
            SpatieQuery::Filter  => static::$FILTER_METHOD,
            SpatieQuery::Field   => static::$FIELD_METHOD,
            SpatieQuery::Include => static::$INCLUDE_METHOD,
            SpatieQuery::Append  => static::$APPEND_METHOD,
        ];
    }

    /**
     * Helper function that returns matching `getQuery` method name for Spatie request query.
     * Spatie query can be any value defined as keys under getSpatieIdentifierMappings.
     * 
     * @var SpatieQuery $field Spatie request field name
     * 
     * @return string
     */
    public static function getQueryMethodForSpatie(SpatieQuery $field) {
        return "getQuery" . ucfirst(strtolower(static::getSpatieIdentifierMappings()[$field->value]));
    }

    /**
     * Helper function that returns matching `getAuthorizedQuery` method name for Spatie request field.
     * Spatie query can be any value defined as keys under getSpatieIdentifierMappings.
     * 
     * @var SpatieQuery $field Spatie request field name
     * 
     * @return string
     */
    public static function getAuthorizedQueryMethodForSpatie(SpatieQuery $field) {
        return "getAuthorizedQuery" . ucfirst(strtolower(static::getSpatieIdentifierMappings()[$field->value]));
    }

     /**
     * Optimization data.
     * 
     * @var array
     * @var bool array[TYPE][0] - reload authorization data, if set to true, it will force 
     * `getAuthorizedQueryTYPE` to reload authorized fields from policy
     * @var bool array[TYPE][1] - authorized fields
     * 
     * @return array
     */
    protected $queryData = array();
    
     /**
     * Get the method name for the attribute query ability in the model policy.
     *
     * @param  string  $type
     * @param  string  $attribute
     * @return string
     */
    public function getAbilityMethodFor($type, $attribute)
    {
        return static::getPolicyMappings()[$type] . Str::studly($attribute);
    }

    /**
     * Get all queryable attributes of specific type for current user.
     *
     * @param  string  $type
     * @param  array   $fields
     * @return array
     */
    public function getQueryableAttributesFor(string $type, array $fields, bool $forceUpdate = false)
    {
        // The key is undefined, return empty list.
        if(! array_key_exists($type, static::getPolicyMappings()))
        {
            return [];
        }
        // Key is valid, but the data is uninitialized.
        else if(! array_key_exists($type, $this->queryData)) 
        {
            $this->queryData[$type] = new stdClass;
            $this->queryData[$type]->update = true;
            $this->queryData[$type]->data = array();
        }
        // Key exists and is initialized, but the object was 
        // updated recently. Ensure that the data is refreshed.
        else if ($this->isDirty())
        {
            $this->queryData[$type]->update = true;
        }

        // Check if reload needed (or initialization)
        if ($this->queryData[$type]->update || $forceUpdate) {

            $policy = Gate::getPolicyFor(static::class);

            // Obtain new rules
            if ($policy) {
                $this->queryData[$type]->data = AttributeGate::getQueryable($this, $type, $fields, $policy);
            } else {
                $this->queryData[$type]->data = $fields;
            }

            // no need to continue updating
            $this->queryData[$type]->update = false;
        }

        return $this->queryData[$type]->data;
    }
}