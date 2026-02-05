<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Check if user already exists to avoid duplicate entry error
        if (!User::where('email', 'it.rayandra@gmail.com')->exists()) {
            User::create([
                'name' => 'Super Admin',
                'email' => 'it.rayandra@gmail.com',
                'password' => Hash::make('@R4y4ndr4'),
                'role' => 'admin',
            ]);
        }
    }
}
