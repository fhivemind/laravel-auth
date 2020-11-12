<?php

namespace App\Models;

class Referral extends BaseModel
{
    /**
     * Table configuration
     */
    public $table = 'referral';
    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = [
        'user_id',
        'referral_user_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'referral_user_id' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public function getValidationRules()
    {
        return [
            'created_at' => 'nullable',
            'user_id' => 'required|integer',
            'referral_user_id' => 'required|integer'
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function referralUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'referral_user_id');
    }
}
