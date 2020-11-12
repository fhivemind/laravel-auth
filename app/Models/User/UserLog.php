<?php

namespace App\Models;

class UserLog extends BaseModel
{
    /**
     * Table configuration
     */
    public $table = 'user_logs';
    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = [
        'operation',
        'scope',
        'description',
        'uuid_user'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'uuid' => 'string',
        'operation' => 'string',
        'scope' => 'string',
        'description' => 'string',
        'created_at' => 'datetime',
        'uuid_user' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public function getValidationRules()
    {
        return [
            'operation' => 'required',
            'scope' => 'required',
            'description' => 'nullable',
            'created_at' => 'nullable',
            'uuid_user' => 'required'
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'uuid_user');
    }
}
