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
    protected $fillableOriginal = null;

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
     * Get all fillable attributes that current user can edit.
     *
     * @return array
     */
    public function getAuthorizedEditableAttributes()
    {
        // Check if object has been updated, and if so
        // make sure to update fillable attribute
        if ($this->isDirty()) {
            $this->setFillable = true;
        }

        // Check if fillable needs reload
        if ($this->setFillable) {

            // Set default fillableOriginal if undeclared
            if(is_null($this->fillableOriginal)) {
                $this->fillableOriginal = $this->fillable;
            }

            $policy = Gate::getPolicyFor(static::class);

            // Obtain new rules
            if ($policy) {
                $this->fillable = AttributeGate::getFillable($this, $this->fillableOriginal, $policy);
            }

            // no need to continue updating
            $this->setFillable = false;
        }

        return $this->fillable;
    }

    /**
     * Get all fillable attributes that current user cannot edit.
     *
     * @return array
     */
    public function getForbiddenEditableAttributes()
    {
        // Set default fillableOriginal if undeclared
        if(is_null($this->fillableOriginal)) {
            $this->fillableOriginal = $this->fillable;
        }

        $policy = Gate::getPolicyFor(static::class);

        if (! $policy) {
            return [];
        }

        return AttributeGate::getFillable($this, $this->fillableOriginal, $policy, false);
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