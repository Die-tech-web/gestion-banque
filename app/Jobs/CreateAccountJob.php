<?php

namespace App\Jobs;

use App\Events\SendClientNotification;
use App\Models\Client;
use App\Models\Compte;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateAccountJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $clientData;
    protected $compteData;
    protected $isNewClient;

    /**
     * Create a new job instance.
     */
    public function __construct(array $clientData, array $compteData, bool $isNewClient = false)
    {
        $this->clientData = $clientData;
        $this->compteData = $compteData;
        $this->isNewClient = $isNewClient;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::beginTransaction();

        try {
            $client = null;

            // Vérifier si le client existe par ID ou par NCI
            if (isset($this->clientData['id'])) {
                $client = Client::findOrFail($this->clientData['id']);
            } elseif (isset($this->clientData['nci'])) {
                $client = Client::where('nci', $this->clientData['nci'])->first();
                if ($client) {
                    // Utiliser le client existant trouvé par NCI
                    $this->isNewClient = false; // Marquer comme client existant
                }
            }

            // Si client non trouvé, créer un nouveau client
            if (!$client) {
                // Créer un nouvel utilisateur
                $password = Str::random(12);
                $codeAuthentification = Str::random(6);
                $email = $this->clientData['email'];
                $telephone = $this->clientData['telephone'];

                // Vérifier si un utilisateur ou un client avec cet email existe déjà
                if (User::where('email', $email)->exists() || Client::where('email', $email)->exists()) {
                    throw new \Exception('Un utilisateur ou un client avec cet email existe déjà.');
                }

                // Vérifier si un client avec ce téléphone existe déjà
                if (Client::where('telephone', $telephone)->exists()) {
                    throw new \Exception('Un client avec ce numéro de téléphone existe déjà.');
                }

                $user = User::create([
                    'name' => $this->clientData['titulaire'],
                    'email' => $email,
                    'password' => Hash::make($password),
                ]);

                // Créer le client
                $client = Client::create([
                    'user_id' => $user->id,
                    'adresse' => $this->clientData['adresse'],
                    'telephone' => $telephone,
                    'nci' => $this->clientData['nci'],
                    'email' => $email, // Ajouter l'email au client
                    'code_authentification' => $codeAuthentification,
                ]);

                // Stocker les informations pour la notification
                $this->clientData['password'] = $password;
                $this->clientData['code_authentification'] = $codeAuthentification;
            }

            // Générer un numéro de compte unique
            $numeroCompte = $this->generateNumeroCompte();

            // Créer le compte
            $compte = Compte::create([
                'numeroCompte' => $numeroCompte,
                'client_id' => $client->id,
                'type' => $this->compteData['type'],
                'devise' => $this->compteData['devise'],
                'dateCreation' => now(),
                'statut' => 'actif',
                'derniereModification' => now(),
                'version' => 1,
            ]);

            // Créer une transaction initiale pour le solde initial
            $compte->transactions()->create([
                'type' => 'depot',
                'montant' => $this->compteData['soldeInitial'],
                'description' => 'Solde initial',
                'dateTransaction' => now(),
            ]);

            DB::commit();

            // Envoyer les notifications si c'est un nouveau client
            if ($this->isNewClient) {
                event(new SendClientNotification($client, $this->clientData['password'], $this->clientData['code_authentification']));
            }

        } catch (\Exception $e) {
            DB::rollBack();
            // Log l'erreur pour debugging
            \Log::error('Erreur lors de la création asynchrone du compte: ' . $e->getMessage());
            throw $e; // Re-throw pour que le job soit marqué comme échoué
        }
    }

    private function generateNumeroCompte(): string
    {
        do {
            $numero = 'C' . str_pad(mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT);
        } while (Compte::where('numeroCompte', $numero)->exists());

        return $numero;
    }
}
