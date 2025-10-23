<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\AdminSeeder;
use Database\Seeders\ClientSeeder;
use Database\Seeders\ComptesSeeder;
use Database\Seeders\TransactionsSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            AdminSeeder::class,
            ClientSeeder::class,
            ComptesSeeder::class,
            TransactionsSeeder::class,
        ]);
    }
}
