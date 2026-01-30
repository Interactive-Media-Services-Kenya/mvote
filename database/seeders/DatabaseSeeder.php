<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            EventSeeder::class,
        ]);

        $adminRole = \App\Models\Role::where('name', 'admin')->first();

        User::firstOrCreate(
            ['phone' => '254706783789'],
            [
                'nick_name' => 'Admin',
                'role_id' => $adminRole->id,
            ]
        );
    }
}
