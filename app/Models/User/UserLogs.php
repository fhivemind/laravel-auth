<?php

namespace App\Models;

class UserLogs extends BaseModel
{
    /**
     * Table configuration
     */
    public $table = 'user_logs';
    const UPDATED_AT = '';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = [
        'operation',
        'scope',
        'description',
        'id_user'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'operation' => 'string',
        'scope' => 'string',
        'description' => 'string',
        'created_at' => 'datetime',
        'id_user' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'operation' => 'required|string',
        'scope' => 'required|string',
        'description' => 'nullable|string',
        'created_at' => 'required',
        'id_user' => 'required|integer'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'id_user');
    }
}
