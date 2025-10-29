<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Client as AppClient;
use App\Models\Compte;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Passport\Passport;
use Laravel\Passport\Client as PassportClient;
use Carbon\Carbon;

class CompteBlockTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $clientUser;
    protected $compteEpargne;
    protected $compteCourant;
    protected $adminToken;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Passport clients programmatically for tests
        $personalClient = PassportClient::factory()->create([
            'name' => 'Personal Access Client',
            'secret' => 'some-secret',
            'redirect' => 'http://localhost',
            'personal_access_client' => true,
            'password_client' => false,
            'revoked' => false,
        ]);

        $passwordClient = PassportClient::factory()->create([
            'name' => 'Password Grant Client',
            'secret' => 'another-secret',
            'redirect' => 'http://localhost',
            'personal_access_client' => false,
            'password_client' => true,
            'revoked' => false,
        ]);

        // Set the client IDs in the config for the test run
        config(['passport.personal_access_client.id' => $personalClient->id]);
        config(['passport.password_grant_client.id' => $passwordClient->id]);

        // Explicitly set the personal access client for Passport in tests
        Passport::personalAccessClient($personalClient);

        // Créer un admin pour les tests
        $this->adminUser = User::factory()->create([
            'email' => 'admin@test.com',
            'name' => 'Admin Test',
            'password' => bcrypt('password')
        ]);
        Admin::create([
            'user_id' => $this->adminUser->id,
            'matricule' => 'ADM001'
        ]);
        $this->adminToken = $this->adminUser->createToken('test-token')->accessToken;

        // Créer un client pour les tests
        $this->clientUser = User::factory()->create([
            'email' => 'client@test.com',
            'name' => 'Client Test'
        ]);
        $client = AppClient::create([
            'user_id' => $this->clientUser->id,
            'telephone' => '+221771234567',
            'nci' => '12345678901234567890',
            'adresse' => 'Test Address'
        ]);

        // Créer un compte épargne de test
        $this->compteEpargne = Compte::create([
            'client_id' => $client->id,
            'numeroCompte' => 'C00123456',
            'type' => 'epargne',
            'devise' => 'FCFA',
            'statut' => 'actif',
            'dateCreation' => now(),
            'version' => 1
        ]);

        // Créer un compte courant de test
        $this->compteCourant = Compte::create([
            'client_id' => $client->id,
            'numeroCompte' => 'C00789012',
            'type' => 'courant',
            'devise' => 'FCFA',
            'statut' => 'actif',
            'dateCreation' => now(),
            'version' => 1
        ]);
    }

    public function test_block_compte_epargne_successfully()
    {
        $blockData = [
            'motif' => 'Activité suspecte',
            'dateBlocage' => Carbon::now()->toDateString(),
            'dateDeblocagePrevue' => Carbon::now()->addMonth()->toDateString()
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ])->postJson("/api/v1/comptes/{$this->compteEpargne->id}/bloquer", $blockData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Compte bloqué avec succès'
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'statut',
                    'motifBlocage',
                    'dateBlocage',
                    'dateDeblocagePrevue'
                ]
            ]);

        $this->compteEpargne->refresh();
        $this->assertEquals('bloque', $this->compteEpargne->statut);
        $this->assertEquals('Activité suspecte', $this->compteEpargne->motifBlocage);
        $this->assertEquals(Carbon::now()->toDateString(), $this->compteEpargne->dateBlocage->toDateString());
        $this->assertEquals(Carbon::now()->addMonth()->toDateString(), $this->compteEpargne->dateDeblocagePrevue->toDateString());
    }

    public function test_block_compte_courant_fails()
    {
        $blockData = [
            'motif' => 'Activité suspecte',
            'dateBlocage' => Carbon::now()->toDateString(),
            'dateDeblocagePrevue' => Carbon::now()->addMonth()->toDateString()
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ])->postJson("/api/v1/comptes/{$this->compteCourant->id}/bloquer", $blockData);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Un compte chèque ne peut pas être bloqué.'
            ]);

        $this->compteCourant->refresh();
        $this->assertEquals('actif', $this->compteCourant->statut);
    }

    public function test_block_already_blocked_compte_fails()
    {
        $this->compteEpargne->update([
            'statut' => 'bloque',
            'motifBlocage' => 'Fraude',
            'dateBlocage' => Carbon::now(),
            'dateDeblocagePrevue' => Carbon::now()->addMonth()
        ]);

        $blockData = [
            'motif' => 'Nouvelle activité suspecte',
            'dateBlocage' => Carbon::now()->toDateString(),
            'dateDeblocagePrevue' => Carbon::now()->addMonth()->toDateString()
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ])->postJson("/api/v1/comptes/{$this->compteEpargne->id}/bloquer", $blockData);

        $response->assertStatus(409) // Conflict status code
            ->assertJson([
                'success' => false,
                'message' => 'Le compte est déjà bloqué.'
            ]);

        $this->compteEpargne->refresh();
        $this->assertEquals('bloque', $this->compteEpargne->statut);
        $this->assertEquals('Fraude', $this->compteEpargne->motifBlocage); // Motif should not change
    }

    public function test_block_non_existent_compte_fails()
    {
        $blockData = [
            'motif' => 'Activité suspecte',
            'dateBlocage' => Carbon::now()->toDateString(),
            'dateDeblocagePrevue' => Carbon::now()->addMonth()->toDateString()
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ])->postJson("/api/v1/comptes/non-existent-id/bloquer", $blockData);

        $response->assertStatus(422) // Expect 422 for validation error (invalid UUID)
            ->assertJsonValidationErrors(['id']); // Assert that 'id' field has validation errors
    }

    public function test_unblock_compte_successfully()
    {
        $this->compteEpargne->update([
            'statut' => 'bloque',
            'motifBlocage' => 'Fraude',
            'dateBlocage' => Carbon::now(),
            'dateDeblocagePrevue' => Carbon::now()->addMonth()
        ]);

        $unblockData = [
            'motif' => 'Vérification complétée'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ])->postJson("/api/v1/comptes/{$this->compteEpargne->id}/debloquer", $unblockData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Compte débloqué avec succès'
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'statut',
                    'dateDeblocage'
                ]
            ]);

        $this->compteEpargne->refresh();
        $this->assertEquals('actif', $this->compteEpargne->statut);
        $this->assertNull($this->compteEpargne->motifBlocage);
        $this->assertNull($this->compteEpargne->dateBlocage);
        $this->assertNull($this->compteEpargne->dateDeblocagePrevue);
    }

    public function test_unblock_unblocked_compte_fails()
    {
        $this->compteEpargne->update([
            'statut' => 'actif',
            'motifBlocage' => null,
            'dateBlocage' => null,
            'dateDeblocagePrevue' => null
        ]);

        $unblockData = [
            'motif' => 'Vérification complétée'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ])->postJson("/api/v1/comptes/{$this->compteEpargne->id}/debloquer", $unblockData);

        $response->assertStatus(409) // Conflict status code
            ->assertJson([
                'success' => false,
                'message' => 'Le compte n\'est pas bloqué.'
            ]);

        $this->compteEpargne->refresh();
        $this->assertEquals('actif', $this->compteEpargne->statut);
    }

    public function test_unblock_non_existent_compte_fails()
    {
        $unblockData = [
            'motif' => 'Vérification complétée'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ])->postJson("/api/v1/comptes/non-existent-id/debloquer", $unblockData);

        $response->assertStatus(422) // Expect 422 for validation error (invalid UUID)
            ->assertJsonValidationErrors(['id']); // Assert that 'id' field has validation errors
    }

    public function test_compte_automatically_unblocks_after_expiration()
    {
        // Set up a blocked account with an expired deblocking date
        $this->compteEpargne->update([
            'statut' => 'bloque',
            'motifBlocage' => 'Temporaire',
            'dateBlocage' => Carbon::now()->subMonth(),
            'dateDeblocagePrevue' => Carbon::now()->subDay() // Expired yesterday
        ]);

        // Access the account details, which should trigger the automatic unblock
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ])->getJson("/api/v1/comptes/{$this->compteEpargne->id}");

        $response->assertStatus(200);

        $this->compteEpargne->refresh();
        $this->assertEquals('actif', $this->compteEpargne->statut);
        $this->assertNull($this->compteEpargne->motifBlocage);
        $this->assertNull($this->compteEpargne->dateBlocage);
        $this->assertNull($this->compteEpargne->dateDeblocagePrevue);
    }

    public function test_block_compte_unauthorized()
    {
        $blockData = [
            'motif' => 'Activité suspecte',
            'dateBlocage' => Carbon::now()->toDateString(),
            'dateDeblocagePrevue' => Carbon::now()->addMonth()->toDateString()
        ];

        // Test sans token
        $response = $this->postJson("/api/v1/comptes/{$this->compteEpargne->id}/bloquer", $blockData);
        $response->assertStatus(401);

        // Test avec un token client (non admin)
        Passport::actingAs($this->clientUser);
        $clientToken = $this->clientUser->createToken('client-token')->accessToken;
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $clientToken,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ])->postJson("/api/v1/comptes/{$this->compteEpargne->id}/bloquer", $blockData);
        $response->assertStatus(403); // Forbidden
    }

    public function test_unblock_compte_unauthorized()
    {
        $this->compteEpargne->update([
            'statut' => 'bloque',
            'motifBlocage' => 'Fraude',
            'dateBlocage' => Carbon::now(),
            'dateDeblocagePrevue' => Carbon::now()->addMonth()
        ]);

        $unblockData = [
            'motif' => 'Vérification complétée'
        ];

        // Test sans token
        $response = $this->postJson("/api/v1/comptes/{$this->compteEpargne->id}/debloquer", $unblockData);
        $response->assertStatus(401);

        // Test avec un token client (non admin)
        Passport::actingAs($this->clientUser);
        $clientToken = $this->clientUser->createToken('client-token')->accessToken;
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $clientToken,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ])->postJson("/api/v1/comptes/{$this->compteEpargne->id}/debloquer", $unblockData);
        $response->assertStatus(403); // Forbidden
    }
}
