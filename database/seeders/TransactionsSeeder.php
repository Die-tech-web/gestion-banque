<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transaction;
use App\Models\Compte;

class TransactionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $compte1 = Compte::where('numeroCompte', 'C00123456')->first();
        $compte2 = Compte::where('numeroCompte', 'C00123457')->first();
        $compte3 = Compte::where('numeroCompte', 'C00123458')->first();
        $compte4 = Compte::where('numeroCompte', 'C00123459')->first();

        if ($compte1) {
            Transaction::create([
                'compte_id' => $compte1->id,
                'type' => 'depot',
                'montant' => 1000000,
                'devise' => 'XOF',
                'description' => 'Dépôt initial',
                'dateTransaction' => now(),
            ]);
            Transaction::create([
                'compte_id' => $compte1->id,
                'type' => 'retrait',
                'montant' => 50000,
                'devise' => 'XOF',
                'description' => 'Retrait DAB',
                'dateTransaction' => now(),
            ]);
        }

        if ($compte2) {
            Transaction::create([
                'compte_id' => $compte2->id,
                'type' => 'depot',
                'montant' => 500000,
                'devise' => 'XOF',
                'description' => 'Dépôt initial',
                'dateTransaction' => now(),
            ]);
            Transaction::create([
                'compte_id' => $compte2->id,
                'type' => 'retrait',
                'montant' => 100000,
                'devise' => 'XOF',
                'description' => 'Paiement facture',
                'dateTransaction' => now(),
            ]);
        }

        if ($compte3) {
            Transaction::create([
                'compte_id' => $compte3->id,
                'type' => 'depot',
                'montant' => 250000,
                'devise' => 'XOF',
                'description' => 'Dépôt initial',
                'dateTransaction' => now(),
            ]);
            Transaction::create([
                'compte_id' => $compte3->id,
                'type' => 'retrait',
                'montant' => 50000,
                'devise' => 'XOF',
                'description' => 'Achat en ligne',
                'dateTransaction' => now(),
            ]);
        }

        if ($compte4) {
            Transaction::create([
                'compte_id' => $compte4->id,
                'type' => 'depot',
                'montant' => 300000,
                'devise' => 'XOF',
                'description' => 'Dépôt initial',
                'dateTransaction' => now(),
            ]);
        }
    }
}
