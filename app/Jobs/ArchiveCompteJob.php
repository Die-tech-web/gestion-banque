<?php

namespace App\Jobs;

use App\Models\Compte;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ArchiveCompteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $compteId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $compteId)
    {
        $this->compteId = $compteId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Récupérer le compte depuis la base principale
        $compte = Compte::withTrashed()->find($this->compteId);

        if (!$compte) {
            \Log::warning("Compte {$this->compteId} non trouvé pour archivage");
            return;
        }

        // Préparer les données à archiver
        $compteData = [
            'original_id' => $compte->id,
            'numerocompte' => $compte->numeroCompte,
            'client_id' => $compte->client_id,
            'type' => $compte->type,
            'devise' => $compte->devise,
            'datecreation' => $compte->dateCreation,
            'statut' => $compte->statut,
            'motifblocage' => $compte->motifBlocage,
            'dateblocage' => $compte->dateBlocage,
            'datedeblocageprevue' => $compte->dateDeblocagePrevue,
            'dernieremodification' => $compte->derniereModification,
            'version' => $compte->version,
            'datefermeture' => $compte->dateFermeture,
            'archived_at' => now(),
        ];

        // Utiliser la connexion Neon pour l'archivage
        DB::connection('neon')->table('archived_comptes')->insert($compteData);

        // Supprimer le compte de la base principale (soft delete déjà fait)
        // Le compte est déjà soft deleted, mais on s'assure qu'il est retiré des données actives
        // Rien à faire ici car le soft delete retire déjà de la base active

        \Log::info("Compte {$this->compteId} archivé avec succès dans Neon");
    }
}
