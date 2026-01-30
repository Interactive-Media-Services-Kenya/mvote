<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class JudgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $judgeRole = Role::where('name', 'judge')->first();

        $judges = [
            [
                'nick_name' => 'Polycarp (Sauti Sol)',
                'phone' => '254711122233',
                'role_id' => $judgeRole->id,
            ],
            [
                'nick_name' => 'Muthoni MQ',
                'phone' => '254711445566',
                'role_id' => $judgeRole->id,
            ],
            [
                'nick_name' => 'Bien-Aimé',
                'phone' => '254711789789',
                'role_id' => $judgeRole->id,
            ],
        ];

        foreach ($judges as $judge) {
            User::firstOrCreate(
                ['phone' => $judge['phone']],
                $judge
            );
        }
    }
}
