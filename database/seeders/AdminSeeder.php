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
        $adminUser = User::where('email', 'dieniang32@gmail.com')->first();

        if ($adminUser) {
            Admin::firstOrCreate([
                'user_id' => $adminUser->id,
            ], [
                'matricule' => 'ADM001',
            ]);
        }

        // Créer un admin supplémentaire si nécessaire
        $adminUser2 = User::where('email', 'admin@example.com')->first();

        if ($adminUser2) {
            Admin::firstOrCreate([
                'user_id' => $adminUser2->id,
            ], [
                'matricule' => 'ADM002',
            ]);
        }
    }
}
