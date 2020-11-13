<?php

use App\Models\UserStatus;

class UserStatusSeeder extends BaseSeeder
{
    public function runAlways()
    {
        UserStatus::create([
            'name' => 'active',
            'description' => 'Active & approved',
        ]);
        
        UserStatus::create([
            'name' => 'inactive',
            'description' => 'Waiting for approval',
        ]);

        UserStatus::create([
            'name' => 'blocked',
            'description' => 'Banned user',
        ]);
    }
}
