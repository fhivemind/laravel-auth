<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Gate;

class AttributeGate
{
    /**
     * 
     * Gets all hidden attributes based on model policy.
     * 
     * @param $model Model
     * @param $fields array default fillable attributes
     * @param $policy model policy
     */
    public static function getHidden(Model $model, $fields, $policy)
    {
        return array_values(array_filter($fields, function ($attribute) use ($model, $policy) {
            $ability = $model->getAttributeViewAbilityMethod($attribute);

            if (is_callable([$policy, $ability])) {
                return Gate::denies($ability, $model);
            }

            return true;
        }));
    }

    /**
     * 
     * Gets all fillable attributes based on model policy.
     * 
     * @param $model Model
     * @param $fields array default fillable attributes
     * @param $policy model policy
     * @param $mode bool defines which type of array it should return
     * `$mode = true` returns all attributes user can edit
     * `$mode = false` returns all attributes user cannot edit
     */
    public static function getFillable(Model $model, $fields, $policy, $mode = true)
    {
        return array_values(array_filter($fields, function ($attribute) use ($model, $policy, $mode) {
            $ability = $model->getAttributeUpdateAbilityMethod($attribute);

            if (is_callable([$policy, $ability])) {
                return $mode ? 
                    Gate::allows($ability, $model) : 
                    Gate::denies($ability, $model);
            }

            return $mode;
        }));
    }

    /**
     * 
     * Gets all query attributes based on model policy.
     * 
     * @param $model Model
     * @param $type query type
     * @param $fields array query attributes
     * @param $policy model policy
     * @param $mode bool defines which type of array it should return
     * `$mode = true` returns all attributes user can query
     * `$mode = false` returns all attributes user cannot query
     */
    public static function getQueryable(Model $model, $type, $fields, $policy, $mode = true)
    {
        return array_values(array_filter($fields, function ($attribute) use ($model, $policy, $type, $mode) {
            $ability = $model->getAttributeQueryAbilityMethodFor($type, $attribute);

            if (is_callable([$policy, $ability])) {
                return $mode ? 
                    Gate::allows($ability, $model) : 
                    Gate::denies($ability, $model);
            }

            return $mode;
        }));
    }
}