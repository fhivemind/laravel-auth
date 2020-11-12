<?php

use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;

class UserRoleSeeder extends BaseSeeder
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
        $users = User::all();

        UserRole::create([
            'active' => true,
            'id_role' => $roles->where('name', '=', 'admin')->first()->id,
            'uuid_user' => $users->where('username', '=', 'Admin')->first()->uuid
        ]);

        for ($i = 0; $i < 5; ++$i) {
            UserRole::firstOrCreate([
                'active' => true,
                'id_role' => $roles->random()->id,
                'uuid_user' => $users->random()->uuid,
            ]);
        }
    }
}
