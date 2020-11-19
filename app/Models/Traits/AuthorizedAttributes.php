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
      * Optimization attributes
      */
    protected $setHidden = true;
    protected $hiddenOriginal = null;
    protected $setFillable = true;
    protected $authorizedFillable = [];

    /**
     * Get the hidden attributes for the model. Drops all attributes
     * defined by Policy which current user can see.
     *
     * @return array
     */
    public function getHidden()
    {
        // Check if object has been updated, and if so
        // make sure to update hidden attribute
        if ($this->isDirty()) {
            $this->setHidden = true;
        }

        // Check if hidden needs reload
        if ($this->setHidden) {

            // Set default hiddenOriginal if undeclared
            if(is_null($this->hiddenOriginal)) {
                $this->hiddenOriginal = $this->hidden;
            }

            $policy = Gate::getPolicyFor(static::class);

            // Obtain new rules
            if ($policy) {
                $this->hidden = AttributeGate::getHidden($this, $this->hiddenOriginal, $policy);
            }

            // no need to continue updating
            $this->setHidden = false;
        }

        return $this->hidden;
    }

    /**
     * Get the fillable attributes for the model.
     *
     * @return array
     */
    public function getFillable()
    {
        return $this->getAuthorizedEditableAttributes();
    }

    /**
     * Get all fillable attributes that current user can edit.
     *
     * This method caches results unless the model has updated,
     * or `$forceUpdate = true` flag was passed.
     * 
     * @param bool $forceUpdate force permission reverification
     * 
     * @return array
     */
    public function getAuthorizedEditableAttributes($forceUpdate = false)
    {
        // Check if object has been updated, and if so
        // make sure to update related attribute
        if ($this->isDirty()) {
            $this->setFillable = true;
        }

        // Check if fillable needs reload (or initialization)
        if ($this->setFillable || $forceUpdate) {

            $policy = Gate::getPolicyFor(static::class);

            // Obtain new rules
            if ($policy) {
                $this->authorizedFillable = AttributeGate::getFillable($this, $this->fillable, $policy);
            } else {
                $this->authorizedFillable = $this->fillable;
            }

            // no need to continue updating
            $this->setFillable = false;
        }

        return $this->authorizedFillable;
    }

    /**
     * Get all fillable attributes that current user cannot edit.
     *
     * For Forbidden attributes, we don't want to cache results,
     * but verify the permissions each time this is requested.
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
     * Get the method name for the attribute view ability in the model policy.
     *
     * @param  string  $attribute
     * @return string
     */
    public function getAttributeViewAbilityMethod($attribute)
    {
        return 'view' . Str::studly($attribute);
    }

    /**
     * Get the method name for the attribute edit ability in the model policy.
     *
     * @param  string  $attribute
     * @return string
     */
    public function getAttributeUpdateAbilityMethod($attribute)
    {
        return 'edit' . Str::studly($attribute);
    }
}