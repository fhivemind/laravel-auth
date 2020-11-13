<?php

namespace App\Models;

use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Illuminate\Database\Eloquent\Model;
use App\Transformers\BaseTransformer;
use App\Transformers\RestfulTransformer;
use App\APIHelpers;
use Spatie\QueryBuilder\AllowedFilter;

class RestfulModel extends Model
{
    /**
     * Every model should have a primary key, which will be returned to API consumers.
     *
     * @var string ID key
     */
    public $primaryKey = '';

    /**
     * @var bool Set to false for UUID keys
     */
    public $incrementing = true;

    /**
     * @var string Set to string for UUID keys
     */
    protected $keyType = 'int';

    /**
     * These attributes (in addition to primary keys) are not allowed to be updated explicitly through
     *  API routes of update and put. They can still be updated internally by Laravel, and your own code.
     *
     * @var array Attributes to disallow updating on the model
     */
    public $immutableAttributes = ['created_at', 'deleted_at'];

    /**
     * What relations should one model of this entity be returned with, from a relevant controller
     *
     * @var null|array
     */
    public static $itemWith = [];

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
     * Return list of attributes for which the sorting is enabled.
     * 
     * @var array
     */
    public function getAllowedSorts()
    {
        return [$this->primaryKey] + $this->fillable;
    }

    /**
     * Return list of attributes for which the filtering is enabled.
     *
     * @return array
     */
    public function getAllowedFilters()
    {
        return [AllowedFilter::exact($this->primaryKey)] + $this->fillable;
    }

    /**
     * Return list of attributes for which the selecting is enabled.
     *
     * @return array
     */
    public function getAllowedFields()
    {
        return [$this->primaryKey] + $this->fillable;
    }

    /**
     * Return list of attributes for which the eager loading is enabled.
     *
     * @return array
     */
    public function getAllowedIncludes()
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
            if (! empty($model->immutableAttributes)) {
                // For each immutable attribute, check if they have changed
                foreach ($model->immutableAttributes as $attributeName) {
                    if ($model->getOriginal($attributeName) != $model->getAttribute($attributeName)) {
                        throw new BadRequestHttpException('Updating the "'. APIHelpers::formatCaseAccordingToResponseFormat($attributeName) .'" attribute is not allowed.');
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

    /**
     * Get list of eager loads relations.
     *
     * @return array
     */
    public static function getItemWith()
    {
        return static::$itemWith;
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
