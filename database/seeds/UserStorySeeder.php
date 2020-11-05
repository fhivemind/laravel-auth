<?php

use App\Models\Role;
use App\Models\User;

class UserStorySeeder extends BaseSeeder
{
    /**
     * Credentials
     */
    const ADMIN_CREDENTIALS = [
        'email' => 'admin@admin.com',
    ];

    public function runFake()
    {
        // Grab all roles for reference
        $roles = Role::all();

        // Create an admin user
        factory(App\Models\User::class)->create([
            'username' => 'Admin',
            'email'    => static::ADMIN_CREDENTIALS['email']
        ]);

        // Create regular user
        factory(App\Models\User::class)->create([
            'username' => 'Bob',
            'email'    => 'bob@bob.com'
        ]);

        factory(App\Models\UserRole::class)->create([
            'active' => true,
            'id_role' => Role::all()->where('name', '=', 'admin')->first()->$id,
            'id_user' => User::all()->where('username', '=', 'admin')->first()->$id
        ]);
    }
}
