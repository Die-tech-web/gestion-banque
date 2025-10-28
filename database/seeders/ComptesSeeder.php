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
            Compte::updateOrCreate([
                'numeroCompte' => 'C00123480', // Numéro de compte unique
            ], [
                'client_id' => $clientOne->id,
                'type' => 'epargne',
                'devise' => 'XOF',
                'dateCreation' => '2023-01-15',
                'statut' => 'actif',
                'derniereModification' => now(),
                'version' => 1,
            ]);

            Compte::updateOrCreate([
                'numeroCompte' => 'C00123481', // Numéro de compte unique
            ], [
                'client_id' => $clientOne->id,
                'type' => 'cheque', // Compte chèque
                'devise' => 'XOF',
                'dateCreation' => '2023-02-01',
                'statut' => 'actif',
                'derniereModification' => now(),
                'version' => 1,
            ]);
        }

        if ($clientTwo) {
            Compte::updateOrCreate([
                'numeroCompte' => 'C00123482', // Numéro de compte unique
            ], [
                'client_id' => $clientTwo->id,
                'type' => 'epargne',
                'devise' => 'XOF',
                'dateCreation' => '2023-03-10',
                'statut' => 'actif',
                'derniereModification' => now(),
                'version' => 1,
            ]);

            Compte::updateOrCreate([
                'numeroCompte' => 'C00123483', // Numéro de compte unique
            ], [
                'client_id' => $clientTwo->id,
                'type' => 'cheque', // Compte chèque
                'devise' => 'XOF',
                'dateCreation' => '2023-04-22',
                'statut' => 'ferme',
                'derniereModification' => now(),
                'version' => 1,
            ]);

            Compte::updateOrCreate([
                'numeroCompte' => 'C00123484', // Numéro de compte unique
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

        $fabiClient = Client::whereHas('user', function ($query) {
            $query->where('email', 'fabi.fall@example.com');
        })->first();

        $ndiayeClient = Client::whereHas('user', function ($query) {
            $query->where('email', 'ndiaye.savon@example.com');
        })->first();

        if ($fabiClient) {
            Compte::updateOrCreate([
                'numeroCompte' => 'C00123485', // Numéro de compte unique
            ], [
                'client_id' => $fabiClient->id,
                'type' => 'epargne',
                'devise' => 'XOF',
                'dateCreation' => '2023-06-20',
                'statut' => 'actif',
                'derniereModification' => now(),
                'version' => 1,
            ]);
        }

        if ($ndiayeClient) {
            Compte::updateOrCreate([
                'numeroCompte' => 'C00123486', // Numéro de compte unique
            ], [
                'client_id' => $ndiayeClient->id,
                'type' => 'cheque', // Compte chèque
                'devise' => 'XOF',
                'dateCreation' => '2023-07-10',
                'statut' => 'actif',
                'derniereModification' => now(),
                'version' => 1,
            ]);
        }

        $thiernoClient = Client::whereHas('user', function ($query) {
            $query->where('email', 'thierno.segnae@example.com');
        })->first();

        if ($thiernoClient) {
            Compte::updateOrCreate([
                'numeroCompte' => 'C00123470', // Nouveau numéro de compte unique
            ], [
                'client_id' => $thiernoClient->id,
                'type' => 'cheque', // Compte chèque
                'devise' => 'XOF',
                'dateCreation' => '2023-08-01',
                'statut' => 'actif',
                'derniereModification' => now(),
                'version' => 1,
            ]);
        }

        $kalidouClient = Client::whereHas('user', function ($query) {
            $query->where('email', 'kalidou.guisse@example.com');
        })->first();

        if ($kalidouClient) {
            Compte::updateOrCreate([
                'numeroCompte' => 'C00123471', // Nouveau numéro de compte unique
            ], [
                'client_id' => $kalidouClient->id,
                'type' => 'cheque', // Compte chèque
                'devise' => 'XOF',
                'dateCreation' => '2023-09-05',
                'statut' => 'actif',
                'derniereModification' => now(),
                'version' => 1,
            ]);
        }

        $ramaClient = Client::whereHas('user', function ($query) {
            $query->where('email', 'rama.gueye@example.com');
        })->first();

        if ($ramaClient) {
            Compte::firstOrCreate([
                'numeroCompte' => 'C00123472', // Nouveau numéro de compte unique
            ], [
                'client_id' => $ramaClient->id,
                'type' => 'epargne', // Compte épargne
                'devise' => 'XOF',
                'dateCreation' => '2023-10-10',
                'statut' => 'actif',
                'derniereModification' => now(),
                'version' => 1,
            ]);
        }
    }
}
