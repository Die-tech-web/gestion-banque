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
        User::firstOrCreate([
            'email' => 'client1@example.com',
        ], [
            'name' => 'Client One',
            'password' => Hash::make('password'),
        ]);

        User::firstOrCreate([
            'email' => 'client2@example.com',
        ], [
            'name' => 'Client Two',
            'password' => Hash::make('password'),
        ]);

        User::firstOrCreate([
            'email' => 'fabi.fall@example.com',
        ], [
            'name' => 'Fabi Fall',
            'password' => Hash::make('password'),
        ]);

        User::firstOrCreate([
            'email' => 'ndiaye.savon@example.com',
        ], [
            'name' => 'Ndiaye Savon',
            'password' => Hash::make('password'),
        ]);

        User::firstOrCreate([
            'email' => 'dieniang32@gmail.com',
        ], [
            'name' => 'Admin User',
            'password' => Hash::make('password'),
        ]);
    }
}
