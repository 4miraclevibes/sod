<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            ['name' => 'Admin', 'email' => 'admin@example.com', 'password' => Hash::make('password'), 'role_id' => 1],
            ['name' => 'User', 'email' => 'user@example.com', 'password' => Hash::make('password'), 'role_id' => 2],
            ['name' => 'Driver', 'email' => 'driver@example.com', 'password' => Hash::make('password'), 'role_id' => 3],
        ];

        User::insert($users);
    }
}
