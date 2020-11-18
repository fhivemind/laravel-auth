<?php

namespace App\Models\Traits;

use Gate;
use Auth;
use Illuminate\Support\Str;

trait AuthorizedAttributes
{
    /**
     * Get the hidden attributes for the model.
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
     * Get the fillable attributes for the model.
     *
     * @return array
     */
    public function getFillable()
    {
        $policy = Gate::getPolicyFor(static::class);

        if (! $policy) {
            return $this->fillable;
        }

        return AttributeGate::getFillable($this, $this->fillable, $policy);
    }

    /**
     * Get the method name for the attribute visibility ability in the model policy.
     *
     * @param  string  $attribute
     * @return string
     */
    public function getAttributeViewAbilityMethod($attribute)
    {
        return 'view'.Str::studly($attribute);
    }

    /**
     * Get the model policy ability method name to update an model attribute.
     *
     * @param  string  $attribute
     * @return string
     */
    public function getAttributeUpdateAbilityMethod($attribute)
    {
        return 'remake'.Str::studly($attribute);
    }
}