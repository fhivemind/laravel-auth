<?php

namespace App\Models\Traits;

use Gate;
use Illuminate\Support\Str;

trait AuthorizedAttributes
{
    /************************************************************
     * Public interface
     ***********************************************************/
    /**
     * Get the hidden attributes for the model. Drops all attributes
     * defined by Policy which current user can see.
     *
     * @return array
     */
    public function getHidden()
    {
        $policy = Gate::getPolicyFor(static::class);

        if (! $policy) {
            return $this->hidden;
        }

        return AttributeGate::getHidden($this, $this->hidden, $policy);
    }

    /**
     * Get all fillable attributes that current user can edit.
     *
     * @return array
     */
    public function getAllowdEditableAttributes()
    {
        $policy = Gate::getPolicyFor(static::class);

        if (! $policy) {
            return $this->fillable;
        }

        return AttributeGate::getFillable($this, $this->fillable, $policy);
    }

    /**
     * Get all fillable attributes that current user cannot edit.
     *
     * @return array
     */
    public function getForbiddenEditableAttributes()
    {
        $policy = Gate::getPolicyFor(static::class);

        if (! $policy) {
            return [];
        }

        return AttributeGate::getFillable($this, $this->fillable, $policy, false);
    }

    /**
     * Get the method name for the attribute visibility ability in the model policy.
     *
     * @param  string  $attribute
     * @return string
     */
    public function getAttributeViewAbilityMethod($attribute)
    {
        return 'view' . Str::studly($attribute);
    }

    /**
     * Get the model policy ability method name to update an model attribute.
     *
     * @param  string  $attribute
     * @return string
     */
    public function getAttributeUpdateAbilityMethod($attribute)
    {
        return 'edit' . Str::studly($attribute);
    }
}