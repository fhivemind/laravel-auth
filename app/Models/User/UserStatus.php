<?php

namespace App\Models;

use App\Models\Enums\UserStatus as EnumsUserStatus;

class UserStatus extends BaseModel
{
    /**
     * Table configuration
     */
    public $table = 'user_status';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = [
        'name',
        'description'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
        'description' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public function getValidationRules()
    {
        return [
            'name' => 'required|string',
            'description' => 'nullable|string'
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function users()
    {
        return $this->hasMany(\App\Models\User::class, 'id_status');
    }

    public static function getStatusId(EnumsUserStatus $status) {
        return UserStatus::where("name", $status->value)->value('id');
    }
}
