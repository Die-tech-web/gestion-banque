<?php

namespace App\Listeners;

use App\Events\SendClientNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client as TwilioClient;

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
            // Envoyer l'email avec le mot de passe
            $this->sendEmail($event->client, $event->password);

            // Envoyer le SMS avec le code d'authentification
            $this->sendSms($event->client, $event->codeAuthentification);

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi des notifications client: ' . $e->getMessage());
            throw $e; // Relancer l'exception pour que le job échoue et soit retenté
        }
    }

    private function sendEmail($client, $password)
    {
        $user = $client->user;

        Mail::raw(
            "Bonjour {$user->name},\n\n" .
            "Votre compte a été créé avec succès.\n\n" .
            "Voici vos informations de connexion :\n" .
            "Email: {$user->email}\n" .
            "Mot de passe: {$password}\n\n" .
            "Veuillez changer votre mot de passe lors de votre première connexion.\n\n" .
            "Cordialement,\n" .
            "L'équipe Gestion Compte",
            function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Création de votre compte - Informations de connexion');
            }
        );
    }

    private function sendSms($client, $codeAuthentification)
    {
        $twilioSid = config('services.twilio.sid');
        $twilioToken = config('services.twilio.token');
        $twilioFrom = config('services.twilio.from');

        // Vérifier si les credentials Twilio sont configurés
        if (!$twilioSid || !$twilioToken || !$twilioFrom || $twilioSid === 'your_twilio_sid') {
            Log::warning('Twilio credentials not configured. Skipping SMS notification.');
            return;
        }

        try {
            $twilio = new TwilioClient($twilioSid, $twilioToken);

            $twilio->messages->create(
                $client->telephone,
                [
                    'from' => $twilioFrom,
                    'body' => "Votre code d'authentification est : {$codeAuthentification}. Utilisez-le lors de votre première connexion."
                ]
            );
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi du SMS: ' . $e->getMessage());
            // Ne pas relancer l'exception pour éviter l'échec de la création du compte
        }
    }
}
