<?php

namespace App\Models;

use App\Models\Role;
use Hash;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends BaseModel implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract,
    JWTSubject
{
    use Authenticatable, Authorizable, CanResetPassword, Notifiable;

    /**
     * Table configuration
     */
    protected $table = 'user';

    /**
     * @var array Relations to load implicitly by Restful controllers
     */
    public static $itemWith = ['roles'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'email',
        'first_name',
        'last_name',
        'phone_number',
        'active',
        'comment',
        'id_country'
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
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'uuid' => 'string',
        'username' => 'string',
        'email' => 'string',
        'first_name' => 'string',
        'last_name' => 'string',
        'phone_number' => 'string',
        'active' => 'boolean',
        'password' => 'string',
        'token' => 'string',
        'token_expires_at' => 'datetime',
        'comment' => 'string',
        'id_country' => 'integer'
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
            'email' => 'email|max:255|unique:user',
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'active' => 'required|boolean',
            'password' => 'nullable|string',
            'token' => 'nullable|string',
            'token_expires_at' => 'nullable',
            'comment' => 'nullable|string',
            'created_at' => 'nullable',
            'updated_at' => 'nullable',
            'id_country' => 'nullable|integer'
        ];
    }

    /**
     * Return list of attributes for which the eager loading is enabled.
     *
     * @return array
     */
    public function getAllowedIncludes()
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
     * Is this user an admin?
     *
     * @return bool
     */
    public function isAdmin()
    {
        return in_array(Role::ROLE_ADMIN, $this->getRoles());
    }

    /**
     * Is this user an editor?
     *
     * @return bool
     */
    public function isEditor()
    {
        return in_array(Role::ROLE_EDITOR, $this->getRoles());
    }

    /**
     * For Authentication
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * For Authentication
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'user' => [
                'id' => $this->getKey(),
                'email' => $this->email
            ],
        ];
    }

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return $this->getKeyName();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function roles()
    {
        return $this->hasManyThrough(\App\Models\Role::class, \App\Models\UserRole::class, 'uuid_user', 'id', 'uuid', 'id_role');
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
//        return $this->hasMany(\App\Models\ProjectUser::class, 'uuid_user');
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
        return $this->hasMany(\App\Models\UserLog::class, 'uuid_user');
    }

//    /**
//     * @return \Illuminate\Database\Eloquent\Relations\HasMany
//     **/
//    public function tasks()
//    {
//        return $this->hasMany(\App\Models\Task::class, 'uuid_user');
//    }
//
//    /**
//     * @return \Illuminate\Database\Eloquent\Relations\HasMany
//     **/
//    public function organizationUsers()
//    {
//        return $this->hasMany(\App\Models\OrganizationUser::class, 'uuid_user');
//    }
}
