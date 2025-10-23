<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Client One',
            'email' => 'client1@example.com',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Client Two',
            'email' => 'client2@example.com',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);
    }
}
