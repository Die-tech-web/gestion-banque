<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Compte;
use Carbon\Carbon;

class BlockCompteJob implements ShouldQueue
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
        $compte = Compte::find($this->compteId);

        if ($compte && $compte->statut !== 'bloque' && $compte->dateBlocage && Carbon::parse($compte->dateBlocage)->isToday()) {
            $compte->statut = 'bloque';
            $compte->derniereModification = now();
            $compte->save();

            // Dispatch le job d'archivage pour les comptes bloqués par le scheduler
            \App\Jobs\ArchiveCompteJob::dispatch($compte->id);
        }
    }
}
