<?php

use App\Models\Referral;
use App\Models\User;

class UserRefferalSeeder extends BaseSeeder
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
        $users = User::all();

        Referral::firstOrCreate([
            'user_id' => $users->where('username', '=', 'Admin')->first()->id,
            'referral_user_id' => $users->where('username', '=', 'Bob')->first()->id,
        ]);
        
        Referral::firstOrCreate([
            'user_id' => $users->where('username', '=', 'Admin')->first()->id,
            'referral_user_id' => $users->where('username', '=', 'Alice')->first()->id,
        ]);
    }
}
