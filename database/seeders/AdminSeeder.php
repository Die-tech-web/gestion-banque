<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use App\Models\User;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUser = User::where('email', 'admin@example.com')->first();

        if ($adminUser) {
            Admin::create([
                'user_id' => $adminUser->id,
                'matricule' => 'ADM001',
            ]);
        }
    }
}
