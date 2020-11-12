<?php

use App\Models\User;
use App\Models\UserLog;

class UserLogSeeder extends BaseSeeder
{
    public function runFake()
    {
        $users = User::all();
        
        for ($i = 0; $i < 20; ++$i) {
            UserLog::firstOrCreate([
                'operation' => $this->faker->unique()->word(),
                'scope' => $this->faker->unique()->word(),
                'description' => $this->faker->sentence(),
                'uuid_user' => $users->random()->uuid,
            ]);
        }
    }
}
