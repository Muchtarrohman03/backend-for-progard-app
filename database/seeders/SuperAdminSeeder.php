<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\Division;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            [
                'email' => 'superadmin@progard.com',
            ],
            [
                'password' => Hash::make(
                    env('SUPERADMIN_PASSWORD', 'password')
                ),
            ]
        );

        $management = Division::where('name', 'Management')->first();

        UserProfile::updateOrCreate(
            [
                'user_id' => $user->id,
            ],
            [
                'name' => 'Super Administrator',
                'gender' => 'male',
                'division_id' => $management?->id,
            ]
        );

        $user->syncRoles('superadmin');
    }
}
