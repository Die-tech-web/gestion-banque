<?php

namespace App\Jobs;

use App\Models\Compte;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class DebloquerCompteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Récupérer les comptes archivés dans Neon dont la date de déblocage est dépassée
        // Contrainte: Seul les comptes épargne bloqués dont la date de fin de blocage est échue peuvent être désarchivés
        $archivedComptes = DB::connection('neon')
            ->table('archived_comptes')
            ->where('type', 'epargne')
            ->where('statut', 'bloque')
            ->whereNotNull('datedeblocageprevue')
            ->where('datedeblocageprevue', '<=', now())
            ->get();

        foreach ($archivedComptes as $archivedCompte) {
            // Restaurer le compte dans la base principale
            $compteData = (array) $archivedCompte;
            unset($compteData['id']); // Supprimer l'ID de l'archive
            unset($compteData['archived_at']); // Supprimer la date d'archivage
            unset($compteData['original_id']); // Utiliser l'ID original si nécessaire

            // Créer ou mettre à jour le compte dans la base principale
            $compte = Compte::withTrashed()->find($archivedCompte->original_id);

            if ($compte) {
                // Restaurer le compte soft deleted si nécessaire
                if ($compte->trashed()) {
                    $compte->restore();
                }

                // Mettre à jour les champs de blocage
                $compte->statut = 'actif';
                $compte->motifBlocage = null;
                $compte->dateBlocage = null;
                $compte->dateDeblocagePrevue = null;
                $compte->derniereModification = now();
                $compte->save();
            } else {
                // Si le compte n'existe pas, le créer (cas rare)
                $compteData['id'] = $archivedCompte->original_id;
                Compte::create($compteData);
            }

            // Supprimer de l'archive Neon
            DB::connection('neon')
                ->table('archived_comptes')
                ->where('id', $archivedCompte->id)
                ->delete();

            \Log::info("Compte {$archivedCompte->original_id} restauré depuis l'archive Neon");
        }
    }
}
