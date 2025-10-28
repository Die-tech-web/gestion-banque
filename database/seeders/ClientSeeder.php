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

        $fabiUser = User::where('email', 'fabi.fall@example.com')->first();
        $ndiayeUser = User::where('email', 'ndiaye.savon@example.com')->first();

        if ($fabiUser) {
            Client::create([
                'user_id' => $fabiUser->id,
                'adresse' => '789 Boulevard Central',
                'telephone' => '777-888-9999',
                'code_authentification' => 'FAB123',
            ]);
        }

        if ($ndiayeUser) {
            Client::create([
                'user_id' => $ndiayeUser->id,
                'adresse' => '101 Place du Marché',
                'telephone' => '000-111-2222',
                'code_authentification' => 'NDI456',
            ]);
        }

        $thiernoUser = User::where('email', 'thierno.segnae@example.com')->first();
        if ($thiernoUser) {
            Client::create([
                'user_id' => $thiernoUser->id,
                'adresse' => '202 Rue des Artistes',
                'telephone' => '333-444-5555',
                'code_authentification' => 'THI789',
            ]);
        }

        $kalidouUser = User::where('email', 'kalidou.guisse@example.com')->first();
        if ($kalidouUser) {
            Client::create([
                'user_id' => $kalidouUser->id,
                'adresse' => '303 Avenue de la Liberté',
                'telephone' => '666-777-8888',
                'code_authentification' => 'KAL101',
            ]);
        }

        $ramaUser = User::where('email', 'rama.gueye@example.com')->first();
        if ($ramaUser) {
            Client::create([
                'user_id' => $ramaUser->id,
                'adresse' => '404 Place de l\'Indépendance',
                'telephone' => '999-000-1111',
                'code_authentification' => 'RAM202',
            ]);
        }
    }
}
