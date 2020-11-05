<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class UserRole
 * @package App\Models
 * @version November 6, 2020, 3:03 pm UTC
 *
 * @property \App\Models\Role $idRole
 * @property \App\Models\User $idUser
 * @property boolean $active
 * @property integer $id_role
 * @property integer $id_user
 */
class UserRole extends BaseModel
{
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
        'id_user'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'int',
        'active' => 'boolean',
        'id_role' => 'integer',
        'id_user' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'active' => 'required|boolean',
        'created_at' => 'required',
        'id_role' => 'required|integer',
        'id_user' => 'required|integer'
    ];

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
        return $this->belongsTo(\App\Models\User::class, 'id_user');
    }
}
