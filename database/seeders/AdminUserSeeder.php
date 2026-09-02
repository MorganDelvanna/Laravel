<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );
        User::firstOrCreate(
            ['username' => 'calendar'],
            [
                'password' => Hash::make('password'),
                'role' => 'calendar',
            ]
        );
        User::firstOrCreate(
            ['username' => 'news'],
            [
                'password' => Hash::make('password'),
                'role' => 'news',
            ]
        );
    }
}
