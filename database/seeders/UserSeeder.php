<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        User::updateOrCreate(
        ['email' => 'it.rayandra@gmail.com'],
        [
            'name' => 'Super Admin',
            'password' => Hash::make('@R4y4ndr4'),
            'role' => 'admin',
        ]
        );

        // Admin User
        User::updateOrCreate(
        ['email' => 'admin@gmail.com'],
        [
            'name' => 'Admin User',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]
        );

        // Sales User
        User::updateOrCreate(
        ['email' => 'sales@gmail.com'],
        [
            'name' => 'Sales User',
            'password' => Hash::make('password'),
            'role' => 'sales',
        ]
        );
    }
}
