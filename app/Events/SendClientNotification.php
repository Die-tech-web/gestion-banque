<?php

namespace App\Events;

use App\Models\Client;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendClientNotification
{
    use Dispatchable, SerializesModels;

    public $client;
    public $password;
    public $codeAuthentification;

    /**
     * Create a new event instance.
     */
    public function __construct(Client $client, string $password, string $codeAuthentification)
    {
        $this->client = $client;
        $this->password = $password;
        $this->codeAuthentification = $codeAuthentification;
    }
}
