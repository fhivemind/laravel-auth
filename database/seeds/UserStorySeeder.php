<?php

use App\Models\UserStatus;

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
        $status = UserStatus::all();
        
        // Create an admin user
        factory(App\Models\User::class)->create([
            'username' => 'Admin',
            'email'    => static::ADMIN_CREDENTIALS['email'],
            'id_user_status' => $status->where('name', '=', 'active')->first()->id
        ]);

        // Create regular user
        factory(App\Models\User::class)->create([
            'username' => 'Bob',
            'email'    => 'bob@bob.com',
            'id_user_status' => $status->where('name', '=', 'active')->first()->id
        ]);

        // Create regular user
        factory(App\Models\User::class)->create([
            'username' => 'Alice',
            'email'    => 'alice@alice.com',
            'id_user_status' => $status->where('name', '=', 'inactive')->first()->id
        ]);

        // Create banned user
        factory(App\Models\User::class)->create([
            'username' => 'Blocked',
            'email'    => 'blocked@blocked.com',
            'id_user_status' => $status->where('name', '=', 'blocked')->first()->id
        ]);
    }
}
