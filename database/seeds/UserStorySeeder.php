<?php

use App\Models\Referral;
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

        // Create regular user
        factory(App\Models\User::class)->create([
            'username' => 'Alice',
            'email'    => 'alice@alice.com'
        ]);

        Referral::firstOrCreate([
            'user_id' => 1,
            'referral_user_id' => 2,
        ]);
        
        Referral::firstOrCreate([
            'user_id' => 1,
            'referral_user_id' => 3,
        ]);
    }
}
