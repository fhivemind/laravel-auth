<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(RoleTableSeeder::class);
        $this->call(UserStatusSeeder::class);
        $this->call(UserStorySeeder::class);
        $this->call(UserRefferalSeeder::class);
        $this->call(UserRoleSeeder::class);
        $this->call(UserLogSeeder::class);
    }
}
