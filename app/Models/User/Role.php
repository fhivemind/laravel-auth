<?php

namespace App\Models;

class Role extends BaseModel
{
    /**
     * Table configuration
     */
    protected $table = 'role';
    public $timestamps = false;
   
    /**
     * Role constants
     */
    public const ROLE_ADMIN = 'admin';

    /**
     * @var array Relations to load implicitly by Restful controllers
     */
    public static $itemWith = [];

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
     * Validation rules
     *
     * @var array
     */
    public function getValidationRules()
    {
        return [
            'name' => 'required'
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function users()
    {
        return $this->hasMany(\App\Models\UserRole::class, 'id_role');
    }
}
