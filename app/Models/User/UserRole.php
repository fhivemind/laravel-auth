<?php

namespace App\Models;

class UserRole extends BaseModel
{
    /**
     * Table configuration
     */
    protected $table = 'user_role';

    /**
     * @var array Relations to load implicitly by Restful controllers
     */
    public static $itemWith = ['role'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = [
        'active',
        'id_role',
        'uuid_user'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'uuid' => 'string',
        'active' => 'boolean',
        'id_role' => 'integer',
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
            'active' => 'required|boolean',
            'created_at' => 'nullable',
            'id_role' => 'required|integer',
            'uuid_user' => 'required|string'
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function role()
    {
        return $this->belongsTo(\App\Models\Role::class, 'id_role');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'uuid_user');
    }
}