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

        // Grab all roles for reference
        $users = User::all();

        Referral::firstOrCreate([
            'user_id' => $users->where('username', '=', 'Admin')->first()->uuid,
            'referral_user_id' => $users->where('username', '=', 'Bob')->first()->uuid,
        ]);
        
        Referral::firstOrCreate([
            'user_id' => $users->where('username', '=', 'Admin')->first()->uuid,
            'referral_user_id' => $users->where('username', '=', 'Alice')->first()->uuid,
        ]);
    }
}
