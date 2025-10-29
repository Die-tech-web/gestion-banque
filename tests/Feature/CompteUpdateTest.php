<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Client as AppClient; // Alias App\Models\Client
use App\Models\Compte;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Passport\Passport;
use Laravel\Passport\Client as PassportClient; // Alias Laravel\Passport\Client

class CompteUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $clientUser;
    protected $compte;
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
        $client = AppClient::create([ // Use the aliased AppClient
            'user_id' => $this->clientUser->id,
            'telephone' => '+221771234567',
            'nci' => '12345678901234567890',
            'adresse' => 'Test Address'
        ]);

        // Créer un compte de test
        $this->compte = Compte::create([
            'client_id' => $client->id,
            'numeroCompte' => 'C00123456',
            'type' => 'epargne',
            'devise' => 'FCFA',
            'statut' => 'actif',
            'dateCreation' => now(),
            'version' => 1
        ]);
    }

    public function test_update_compte_with_valid_data()
    {
        $updateData = [
            'titulaire' => 'Nouveau Titulaire',
            'informationsClient' => [
                'telephone' => '+221781234568',
                'email' => 'nouveau@test.com',
                'password' => 'newpassword123'
            ]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ])->patchJson("/api/v1/comptes/{$this->compte->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Compte mis à jour avec succès'
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'numeroCompte',
                    'titulaire',
                    'type',
                    'solde',
                    'devise',
                    'dateCreation',
                    'statut',
                    'metadata' => [
                        'derniereModification',
                        'version'
                    ]
                ]
            ]);

        // Vérifier que les données ont été mises à jour
        $this->compte->refresh();
        $this->assertEquals('Nouveau Titulaire', $this->compte->client->user->name);
        $this->assertEquals('+221781234568', $this->compte->client->telephone);
        $this->assertEquals('nouveau@test.com', $this->compte->client->user->email);
    }

    public function test_update_compte_with_empty_data_fails()
    {
        $token = $this->adminUser->createToken('test-token')->accessToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ])->patchJson("/api/v1/comptes/{$this->compte->id}", []);

        $response->assertStatus(422);
    }

    public function test_update_compte_not_found()
    {
        $token = $this->adminUser->createToken('test-token')->accessToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ])->patchJson("/api/v1/comptes/99999", [
                    'titulaire' => 'Test'
                ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Compte non trouvé.'
            ]);
    }

    public function test_update_compte_unauthorized()
    {
        // Test sans token
        $response = $this->patchJson("/api/v1/comptes/{$this->compte->id}", [
            'titulaire' => 'Test'
        ]);

        $response->assertStatus(401);
    }
}
