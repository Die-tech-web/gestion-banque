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

        // Vérifier les contraintes d'archivage
        // Seul les comptes épargne bloqués dont la date de début de blocage est échue peuvent être archivés
        if ($compte->type !== 'epargne' || $compte->statut !== 'bloque') {
            \Log::info("Compte {$this->compteId} non éligible pour archivage (type: {$compte->type}, statut: {$compte->statut})");
            return;
        }

        if (!$compte->dateBlocage || !\Carbon\Carbon::parse($compte->dateBlocage)->isPast() && !\Carbon\Carbon::parse($compte->dateBlocage)->isToday()) {
            \Log::info("Compte {$this->compteId} pas encore éligible pour archivage (date blocage: {$compte->dateBlocage})");
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

        \Log::info("Compte {$this->compteId} archivé avec succès dans Neon");
    }
}
