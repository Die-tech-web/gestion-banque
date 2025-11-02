<?php

namespace App\Listeners;

use App\Events\SendClientNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\AccountCreated; // Will be created in the next step

class SendClientNotificationListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(SendClientNotification $event): void
    {
        try {
            // Envoyer l'email avec le mot de passe et le code d'authentification
            Mail::to($event->client->email)->send(new AccountCreated(
                $event->client,
                $event->password,
                $event->codeAuthentification
            ));

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi des notifications client: ' . $e->getMessage());
            throw $e; // Relancer l'exception pour que le job échoue et soit retenté
        }
    }
}
