<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\User;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clientOneUser = User::where('email', 'client1@example.com')->first();
        $clientTwoUser = User::where('email', 'client2@example.com')->first();

        if ($clientOneUser) {
            Client::create([
                'user_id' => $clientOneUser->id,
                'adresse' => '123 Rue Principale',
                'telephone' => '111-222-3333',
                'code_authentification' => 'ABC123',
            ]);
        }

        if ($clientTwoUser) {
            Client::create([
                'user_id' => $clientTwoUser->id,
                'adresse' => '456 Avenue Secondaire',
                'telephone' => '444-555-6666',
                'code_authentification' => 'XYZ789',
            ]);
        }
    }
}
