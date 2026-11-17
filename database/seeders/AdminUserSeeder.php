<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['UserName' => 'admin'],
            [
                'Password' => 'fivNu-iumK-eg.@2s9n!',
                'Role' => 'admin',
            ]
        );
        User::firstOrCreate(
            ['UserName' => 'calendar'],
            [
                'Password' => 'password',
                'Role' => 'calendar',
            ]
        );
        User::firstOrCreate(
            ['UserName' => 'news'],
            [
                'Password' => 'password',
                'Role' => 'news',
            ]
        );
    }
}
