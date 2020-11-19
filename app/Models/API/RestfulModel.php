<?php

namespace App\Models;

use App\Exceptions\UnauthorizedHttpException;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Illuminate\Database\Eloquent\Model;
use App\Transformers\BaseTransformer;
use App\Transformers\RestfulTransformer;
use App\Helpers;
use App\Models\Traits\AuthorizedAttributes;
use App\Models\Traits\AuthorizedQuery;

class RestfulModel extends Model
{
    use AuthorizedAttributes, AuthorizedQuery;

    /**
     * These attributes (in addition to primary keys) are not allowed to be updated explicitly through
     *  API routes of update and put. They can still be updated internally by Laravel, and your own code.
     *
     * @var array Attributes to disallow updating on the model
     */
    public $immutable = ['created_at', 'deleted_at'];

    /**
     * You can define a custom transformer for a model, if you wish to override the functionality of the Base transformer
     *
     * @var null|RestfulTransformer The transformer to use for this model, if overriding the default
     */
    public static $transformer = null;

    /**
     * Return the validation rules for this model
     *
     * @return array Validation rules to be used for the model when creating it
     */
    public function getValidationRules()
    {
        return [];
    }

    /**
     * Return the validation rules for this model's update operations
     * In most cases, they will be the same as for the create operations
     *
     * @return array Validation roles to use for updating model
     */
    public function getValidationRulesUpdating()
    {
        return $this->getValidationRules();
    }

    /**
     * Return any custom validation rule messages to be used
     *
     * @return array
     */
    public function getValidationMessages()
    {
        return [];
    }

    /**
     * Boot the model
     *
     * Add various functionality in the model lifecycle hooks
     */
    public static function boot()
    {
        parent::boot();

        // Add functionality for creating a model
        static::creating(function (self $model) {
            // If the PK(s) are missing, generate them
            $uuidKeyName = $model->getKeyName();

            if ($uuidKeyName && ! $model->incrementing && ! is_array($uuidKeyName) && ! array_key_exists($uuidKeyName, $model->getAttributes())) {
                $model->$uuidKeyName = Uuid::uuid4()->toString();
            }
        });

        // Add functionality for updating a model
        static::updating(function (self $model) {
            // Disallow updating ID keys
            if ($model->getAttribute($model->getKeyName()) != $model->getOriginal($model->getKeyName())) {
                throw new BadRequestHttpException('Updating the ID of a resource is not allowed.');
            }

            // Disallow updating immutable attributes
            if (! empty($model->immutable)) {
                foreach ($model->immutable as $attributeName) {
                    if ($model->getOriginal($attributeName) != $model->getAttribute($attributeName)) {
                        throw new BadRequestHttpException('Updating the "'. Helpers::formatCaseAccordingToResponseFormat($attributeName) .'" attribute is not allowed.');
                    }
                }
            }

            // Disallow updating unauthorized attributes
            $nonEditableAttributes = $model->getForbiddenEditableAttributes();
            if (! empty($nonEditableAttributes)) {
                foreach ($nonEditableAttributes as $attributeName) {
                    if ($model->getOriginal($attributeName) != $model->getAttribute($attributeName)) {
                        throw new UnauthorizedHttpException('Insufficient permission to update "'. Helpers::formatCaseAccordingToResponseFormat($attributeName) .'" attribute.');
                    }
                }
            }
        });
    }

    /**
     * Return this model's transformer, or a generic one if a specific one is not defined for the model
     *
     * @return BaseTransformer
     */
    public static function getTransformer()
    {
        return is_null(static::$transformer) ? new BaseTransformer : new static::$transformer;
    }

    /**
     * When Laravel creates a new model, it will add any new attributes (such as ID) at the end. When a create
     * operation such as a POST returns the new resource, the ID will thus be at the end, which doesn't look nice.
     * For purely aesthetic reasons, we have this function to conduct a simple reorder operation to move the ID
     * attribute to the head of the attributes array
     *
     * This will be used at the end of create-related controller functions
     *
     * @return void
     */
    public function orderAttributesIdFirst()
    {
        if ($this->getKeyName()) {
            $idValue = $this->getKey();
            unset($this->attributes[$this->getKeyName()]);
            $this->attributes = [$this->getKeyName() => $idValue] + $this->attributes;
        }
    }

    /************************************************************
     * Authorization policies for rest queries
     ***********************************************************/

     /**
     * List of supported relationships that the entity can be returned with.
     * 
     * Its counterpart `getAuthorizedWith()` validates which of these 
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
        return $this->getAuthorizedEditableAttributes();
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
        return $this->getAuthorizedEditableAttributes();
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
        return $this->getAuthorizedEditableAttributes();
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
     * Extending Laravel Functions Below
     ***********************************************************/

    /**
     * We're extending the existing Laravel Builder
     *
     * Create a new Eloquent query builder for the model.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }
}
