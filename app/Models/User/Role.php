<?php

namespace App\Models;

class Role extends BaseModel
{
    /**
     * Role constants
     */
    public const ROLE_ADMIN = 'admin';

    protected $table = 'role';
    public $timestamps = false;

    public static $itemWith = ['users'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'int',
        'name' => 'string',
        'description' => 'string'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function users()
    {
        return $this->hasMany(\App\Models\UserRole::class, 'id_role');
    }
}
