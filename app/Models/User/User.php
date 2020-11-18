<?php

namespace App\Models;

use App\Models\Role;
use Hash;

class User extends BaseModel
{    
    /**
     * Table configuration
     */
    protected $table = 'user';

    /**
     * @var array Relations to load implicitly by Restful controllers
     */
    public function getWithRelationships() {
        return ['roles'];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'first_name',
        'last_name',
        'phone_number',
        'comment',
        'verified_at',
        'id_country',
        'id_status'
    ];

    /**
     * The attributes to disallow updating through API
     * 
     * @var array
     */
    public $immutable = [
        'verified_at',
        'updated_at',
        'created_at'
    ];

    /**
     * The attributes that should be hidden for arrays and API output
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'token',
        'token_expires_at',
        'verification_code'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'username' => 'string',
        'email' => 'string',
        'first_name' => 'string',
        'last_name' => 'string',
        'phone_number' => 'string',
        'password' => 'string',
        'token' => 'string',
        'token_expires_at' => 'datetime',
        'comment' => 'string',
        'verification_code' => 'string',
        'verified_at' => 'datetime',
        'id_country' => 'integer',
        'id_status' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public function getValidationRules()
    {
        return [
            'username' => 'required|min:3',
            'email' => 'max:255|unique:user,email,'.$this->id,
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'password' => 'nullable|string',
            'token' => 'nullable|string',
            'token_expires_at' => 'nullable',
            'comment' => 'nullable|string',
            'verification_code' => 'nullable|string',
            'verified_at' => 'nullable',
            'created_at' => 'nullable',
            'updated_at' => 'nullable',
            'id_country' => 'nullable|integer',
            'id_status' => 'nullable|integer'
        ];
    }

    /**
     * Return list of attributes for which the eager loading is enabled.
     *
     * @return array
     */
    public function getIncludeAttributes()
    {
        return ['logs', 'referrals', 'referred_by'];
    }

    /**
     * Model's boot function
     */
    public static function boot()
    {
        parent::boot();

        static::saving(function (self $user) {
            // Hash user password, if not already hashed
            if (Hash::needsRehash($user->password)) {
                $user->password = Hash::make($user->password);
            }
        });
    }

    /**
     * Get all user's roles
     * 
     * @return array
     * 
     */
    public function getRoles()
    {
        return $this->roles()->pluck('name')->toArray();
    }

    /**
     * Get User status
     * 
     * @return int
     **/
    public function getStatusName()
    {
        $status = $this->status()->pluck('name')->toArray();
        if (count($status) == 0) {
            return null;
        }

        return $status[0];
    }

    /**
     * Is this user active?
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->getStatusName() === UserStatus::ACTIVE;
    }

    /**
     * Is this user banned?
     *
     * @return bool
     */
    public function isBanned()
    {
        return $this->getStatusName() === UserStatus::BLOCKED;
    }

    /**
     * Is this user an admin?
     *
     * @return bool
     */
    public function isAdmin()
    {
        return in_array(Role::ROLE_ADMIN, $this->getRoles());
    }

    /**
     * Is this user just a regular user?
     *
     * @return bool
     */
    public function isRegular()
    {
        return ! $this->isAdmin();
    }

    /**
     * Does this user have specific ability?
     *
     * @return bool
     */
    public function hasAbility($ability)
    {
        return in_array($ability, $this->getRoles());;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function status()
    {
        return $this->belongsTo(\App\Models\UserStatus::class, 'id_status');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function roles()
    {
        return $this->hasManyThrough(\App\Models\Role::class, \App\Models\UserRole::class, 'id_user', 'id', 'id', 'id_role');
    }

//    /**
//     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
//     **/
//    public function idCountry()
//    {
//        return $this->belongsTo(\App\Models\Country::class, 'id_country');
//    }
//    /**
//     * @return \Illuminate\Database\Eloquent\Relations\HasMany
//     **/
//    public function projectUsers()
//    {
//        return $this->hasMany(\App\Models\ProjectUser::class, 'id_user');
//    }
//
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function referrals()
    {
        return $this->hasMany(\App\Models\Referral::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function referredBy()
    {
        return $this->hasOne(\App\Models\Referral::class, 'referral_user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function logs()
    {
        return $this->hasMany(\App\Models\UserLog::class, 'id_user');
    }

//    /**
//     * @return \Illuminate\Database\Eloquent\Relations\HasMany
//     **/
//    public function tasks()
//    {
//        return $this->hasMany(\App\Models\Task::class, 'id_user');
//    }
//
//    /**
//     * @return \Illuminate\Database\Eloquent\Relations\HasMany
//     **/
//    public function organizationUsers()
//    {
//        return $this->hasMany(\App\Models\OrganizationUser::class, 'id_user');
//    }
}
