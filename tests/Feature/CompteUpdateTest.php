<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Client;
use App\Models\Compte;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CompteUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer un admin pour les tests
        $adminUser = User::factory()->create([
            'email' => 'admin@test.com',
            'name' => 'Admin Test'
        ]);
        Admin::create([
            'user_id' => $adminUser->id,
            'matricule' => 'ADM001'
        ]);

        // Créer un client pour les tests
        $clientUser = User::factory()->create([
            'email' => 'client@test.com',
            'name' => 'Client Test'
        ]);
        $client = Client::create([
            'user_id' => $clientUser->id,
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

        $this->adminUser = $adminUser;
        $this->clientUser = $clientUser;
    }

    public function test_update_compte_with_valid_data()
    {
        $token = $this->adminUser->createToken('test-token')->plainTextToken;

        $updateData = [
            'titulaire' => 'Nouveau Titulaire',
            'informationsClient' => [
                'telephone' => '+221781234568',
                'email' => 'nouveau@test.com',
                'password' => 'newpassword123'
            ]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ])->patchJson("/api/v1/die.niang/comptes/{$this->compte->id}", $updateData);

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
        $token = $this->adminUser->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ])->patchJson("/api/v1/die.niang/comptes/{$this->compte->id}", []);

        $response->assertStatus(422);
    }

    public function test_update_compte_not_found()
    {
        $token = $this->adminUser->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ])->patchJson("/api/v1/die.niang/comptes/99999", [
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
        $response = $this->patchJson("/api/v1/die.niang/comptes/{$this->compte->id}", [
            'titulaire' => 'Test'
        ]);

        $response->assertStatus(401);
    }
}
