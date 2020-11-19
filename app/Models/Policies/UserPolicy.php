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
     * @param  AuthenticatedUser  $loggedUser
     * @return mixed
     */
    public function viewAll(AuthenticatedUser $loggedUser)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  AuthenticatedUser  $loggedUser
     * @param  User  $user
     * @return mixed
     */
    public function view(AuthenticatedUser $loggedUser, User $user)
    {
        if($loggedUser->id === $user->id) {
            return true;
        }
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  AuthenticatedUser  $loggedUser
     * @return mixed
     */
    public function create(AuthenticatedUser $loggedUser)
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  AuthenticatedUser  $loggedUser
     * @param  User  $user
     * @return mixed
     */
    public function update(AuthenticatedUser $loggedUser, User $user)
    {
        if($loggedUser->id === $user->id) {
            return true;
        }
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  AuthenticatedUser  $loggedUser
     * @param  User  $user
     * @return mixed
     */
    public function delete(AuthenticatedUser $loggedUser, User $user)
    {
        //
    }

    /**
     * Determine whether the user can update status.
     *
     * @param  AuthenticatedUser  $loggedUser
     * @param  User  $user
     * @return bool
     */
    public function editIdStatus(AuthenticatedUser $loggedUser, User $user)
    {
        return $loggedUser->isAdmin();
    }
}
