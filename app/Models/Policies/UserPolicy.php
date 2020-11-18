<?php

namespace App\Models\Policies;

use App\Models\AuthenticatedUser;
use App\Models\User;
use App\Models\Role;

class UserPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAll(AuthenticatedUser $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return mixed
     */
    public function view(AuthenticatedUser $user, User $model)
    {
        if($user->id === $model->id) {
            return true;
        }
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(AuthenticatedUser $user)
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return mixed
     */
    public function update(AuthenticatedUser $user, User $model)
    {
        if($user->id === $model->id) {
            return true;
        }
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return mixed
     */
    public function delete(AuthenticatedUser $user, User $model)
    {
        //
    }

    public function viewVerificationCode(AuthenticatedUser $user, User $model)
    {
        return true;
    }

    public function remakeComment(AuthenticatedUser $user, User $model)
    {
        return false;
    }
}
