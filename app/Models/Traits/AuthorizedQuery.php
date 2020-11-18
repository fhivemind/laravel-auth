<?php

namespace App\Models\Traits;

use Illuminate\Support\Str;
use Spatie\QueryBuilder\AllowedFilter;
use Gate;

trait AuthorizedQuery
{
    /************************************************************
     * Public interface
     ***********************************************************/
    /**
     * List of method prefix name for the attribute query ability in the model policy.
     */
    protected static $queryWithAbilityMethod    = "with";
    protected static $querySelectAbilityMethod  = "select";
    protected static $queryFilterAbilityMethod  = "filter";
    protected static $querySortAbilityMethod    = "sort";
    protected static $queryIncludeAbilityMethod = "include";
    protected static $queryAppendAbilityMethod  = "append";

    /**
     * List of relations that the entity will be returned with
     * These relations will always be returned with model
     *
     * @var null|array
     */
    public function getWithRelationships() {
        return [];
    }

    /**
     * List of attributes for which sorting is possible through queries
     * 
     * Example: ?sort=-name
     * 
     * @var null|array
     */
    public function getSortAttributes() {
        return [];
    }

    /**
     * List of attributes for which filtering is possible through queries
     *
     * Example: ?filter[name]=john&filter[email]=gmail
     * 
     * @var null|array
     */
    public function getFilterAttributes() {
        return [];
    }

    /**
     * List of attributes for which selecting is possible through queries
     *
     * Example: ?fields[users]=id,name
     * 
     * @var null|array
     */
    public function getSelectAttributes() {
        return [];
    }

    /**
     * List of relations for which loading is possible through queries
     *
     * Example: ?include=posts
     * 
     * @var null|array
     */
    public function getIncludeAttributes() {
        return [];
    }

    /**
     * List of custom attributes that are going to be read from model methods
     * and appended
     * 
     * Implementation: public function getFullnameAttribute() { return "{$this->firstname} {$this->lastname"; }
     * Example: ?append=fullname
     * 
     * @var null|array
     */
    public function getAppendAttributes() {
        return [];
    }
    
    /************************************************************
     * Authorized queries from policy
     ***********************************************************/
    /**
     * Get list of attributes for which the sorting is allowed
     * 
     * @var array
     */
    public function getAllowedSorts()
    {
        return [$this->primaryKey] + $this->getQueryableAttributesFor(
            "sort", 
            $this->getSortAttributes()
        );
    }

    /**
     * Get list of attributes for which the filtering is allowed
     *
     * @return array
     */
    public function getAllowedFilters()
    {
        return [AllowedFilter::exact($this->primaryKey)] + $this->getQueryableAttributesFor(
            "filter", 
            $this->getFilterAttributes()
        );
    }

    /**
     * Get list of attributes for which the selecting is allowed
     *
     * @return array
     */
    public function getAllowedSelects()
    {
        return [$this->primaryKey] + $this->getQueryableAttributesFor(
            "select", 
            $this->getSelectAttributes()
        );
    }

    /**
     * Get list of relations for which eager loading through queries is allowed
     *
     * @return array
     */
    public function getAllowedIncludes()
    {
        return $this->getQueryableAttributesFor(
            "include", 
            $this->getIncludeAttributes()
        );
    }

    /**
     * Get list of custom allowed attributes that are going to be read from model methods
     *
     * @return array
     */
    public function getAllowedAppends()
    {
        return $this->getQueryableAttributesFor(
            "append", 
            $this->getAppendAttributes()
        );
    }

    /**
     * Get list of eager loads relations.
     *
     * @return array
     */
    public function getAllowedWith()
    {
        return $this->getQueryableAttributesFor(
            "with", 
            $this->getWithRelationships()
        );
    }

    /************************************************************
     * Authorization policy
     ***********************************************************/
     /**
     * Defines a list of query attributes to method mappings
     * used to find proper policy method where AbilityMethod is
     * 
     * @var null|array
     */
    protected static function queryMappings() {
        return [
            "with"    => static::$queryWithAbilityMethod,
            "sort"    => static::$querySortAbilityMethod,
            "filter"  => static::$queryFilterAbilityMethod,
            "select"  => static::$querySelectAbilityMethod,
            "include" => static::$queryIncludeAbilityMethod,
            "append"  => static::$queryAppendAbilityMethod,
        ];
    }

     /**
     * Get the method name for the attribute query ability in the model policy.
     *
     * @param  string  $type
     * @param  string  $attribute
     * @return string
     */
    public function getAttributeQueryAbilityMethodFor($type, $attribute)
    {
        $res = static::queryMappings()[$type] . Str::studly($attribute);
        return $res;
    }

    /**
     * Get all queryable attributes for current user.
     *
     * @param  string  $type
     * @param  array   $fields
     * @return array
     */
    public function getQueryableAttributesFor($type, $fields)
    {
        $policy = Gate::getPolicyFor(static::class);

        if (! $policy) {
            return $fields;
        }

        return AttributeGate::getQueryable($this, $type, $fields, $policy);
    }
}