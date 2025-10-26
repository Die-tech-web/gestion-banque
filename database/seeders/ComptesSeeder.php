<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Compte;
use App\Models\Client;

class ComptesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clientOne = Client::whereHas('user', function ($query) {
            $query->where('email', 'client1@example.com');
        })->first();

        $clientTwo = Client::whereHas('user', function ($query) {
            $query->where('email', 'client2@example.com');
        })->first();

        if ($clientOne) {
            Compte::firstOrCreate([
                'numeroCompte' => 'C00123456',
            ], [
                'client_id' => $clientOne->id,
                'type' => 'epargne',
                'devise' => 'XOF',
                'dateCreation' => '2023-01-15',
                'statut' => 'actif',
                'derniereModification' => now(),
                'version' => 1,
            ]);

            Compte::firstOrCreate([
                'numeroCompte' => 'C00123457',
            ], [
                'client_id' => $clientOne->id,
                'type' => 'cheque',
                'devise' => 'XOF',
                'dateCreation' => '2023-02-01',
                'statut' => 'actif',
                'derniereModification' => now(),
                'version' => 1,
            ]);
        }

        if ($clientTwo) {
            Compte::firstOrCreate([
                'numeroCompte' => 'C00123458',
            ], [
                'client_id' => $clientTwo->id,
                'type' => 'epargne',
                'devise' => 'XOF',
                'dateCreation' => '2023-03-10',
                'statut' => 'actif',
                'derniereModification' => now(),
                'version' => 1,
            ]);

            Compte::firstOrCreate([
                'numeroCompte' => 'C00123459',
            ], [
                'client_id' => $clientTwo->id,
                'type' => 'cheque',
                'devise' => 'XOF',
                'dateCreation' => '2023-04-22',
                'statut' => 'ferme',
                'derniereModification' => now(),
                'version' => 1,
            ]);

            Compte::firstOrCreate([
                'numeroCompte' => 'C00123460',
            ], [
                'client_id' => $clientTwo->id,
                'type' => 'epargne',
                'devise' => 'XOF',
                'dateCreation' => '2023-05-15',
                'statut' => 'actif',
                'derniereModification' => now(),
                'version' => 1,
            ]);
        }
    }
}
