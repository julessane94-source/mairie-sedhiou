<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Demande;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CitoyenDemandeTest extends TestCase
{
    use RefreshDatabase;

    private User $citoyen;

    protected function setUp(): void
    {
        parent::setUp();
        $this->citoyen = User::factory()->create(['role' => 'citoyen']);
    }

    /**
     * Test - Citoyen peut voir son dashboard
     */
    public function test_citoyen_can_view_dashboard(): void
    {
        $response = $this->actingAs($this->citoyen)->get('/citoyen/dashboard');
        $response->assertStatus(200);
        $response->assertViewIs('citoyen.dashboard');
    }

    /**
     * Test - Citoyen peut créer une demande
     */
    public function test_citoyen_can_create_demande(): void
    {
        $response = $this->actingAs($this->citoyen)->post('/citoyen/demandes', [
            'titre' => 'Demande de certificat',
            'description' => 'Je demande un certificat de résidence',
            'type' => 'Certificat',
            'priorite' => 'normale',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('demandes', [
            'titre' => 'Demande de certificat',
            'citoyen_id' => $this->citoyen->id,
        ]);
    }

    /**
     * Test - Citoyen peut voir ses demandes
     */
    public function test_citoyen_can_view_own_demandes(): void
    {
        Demande::factory()->create(['citoyen_id' => $this->citoyen->id]);
        
        $response = $this->actingAs($this->citoyen)->get('/citoyen/demandes');
        $response->assertStatus(200);
        $response->assertViewIs('citoyen.demandes.index');
    }

    /**
     * Test - Citoyen peut envoyer un message
     */
    public function test_citoyen_can_send_message(): void
    {
        $demande = Demande::factory()->create(['citoyen_id' => $this->citoyen->id]);
        
        $response = $this->actingAs($this->citoyen)->post(
            "/citoyen/demandes/{$demande->id}/messages",
            ['contenu' => 'Avez-vous des nouvelles?']
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('messages', [
            'demande_id' => $demande->id,
            'expediteur_id' => $this->citoyen->id,
            'contenu' => 'Avez-vous des nouvelles?',
        ]);
    }

    /**
     * Test - Citoyen non authentifié ne peut pas accéder
     */
    public function test_unauthenticated_user_cannot_access_citoyen_dashboard(): void
    {
        $response = $this->get('/citoyen/dashboard');
        $response->assertRedirect('/login');
    }

    /**
     * Test - Agent ne peut pas accéder espace citoyen
     */
    public function test_agent_cannot_access_citoyen_dashboard(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        
        $response = $this->actingAs($agent)->get('/citoyen/dashboard');
        $response->assertStatus(403);
    }
}
