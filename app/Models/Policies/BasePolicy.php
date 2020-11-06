<?php

namespace App\Models\Policies;

use App\Models\User;
use App\Policies\RestfulModelPolicy;

class BasePolicy extends RestfulModelPolicy
{
    /**
     * Process 'global' authorization rules.
     * 
     * Invokes for a specific policy only if exists.
     * If no authorization method exists, this method
     * will not be checked.
     *
     * @param $user
     * @param $ability
     * @return bool
     */
    public function before(User $user, $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    /**
     * Process Restful Policies
     * 
     * Following methods should be extended to support authorization.
     * Default authorization if not implemented: FORBIDDEN
     * 
     * Note: Should only return true if allowed.
     *
     *      > public function create(User $user) {}
     *      > public function viewAll(User $user) {}
     *      > public function view(User $user, Model $model) {}
     *      > public function update(User $user, Model $model) {}
     *      > public function delete(User $user, Model $model) {}
    */
}
