<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Demande;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentDemandeComponentTest extends TestCase
{
    use RefreshDatabase;

    private User $agent;
    private User $citoyen;
    private Demande $demande;

    protected function setUp(): void
    {
        parent::setUp();
        $this->agent = User::factory()->create(['role' => 'agent']);
        $this->citoyen = User::factory()->create(['role' => 'citoyen']);
        $this->demande = Demande::factory()->create([
            'citoyen_id' => $this->citoyen->id,
            'agent_assigne_id' => $this->agent->id,
        ]);
    }

    /**
     * Test - Agent peut voir son dashboard
     */
    public function test_agent_can_view_dashboard(): void
    {
        $response = $this->actingAs($this->agent)->get('/agent/dashboard');
        $response->assertStatus(200);
        $response->assertViewIs('agent.dashboard');
    }

    /**
     * Test - Agent peut voir ses demandes assignées
     */
    public function test_agent_can_view_assigned_demandes(): void
    {
        $response = $this->actingAs($this->agent)->get('/agent/demandes');
        $response->assertStatus(200);
        $response->assertViewIs('agent.demandes.index');
    }

    /**
     * Test - Agent peut voir détails d'une demande assignée
     */
    public function test_agent_can_view_assigned_demande_details(): void
    {
        $response = $this->actingAs($this->agent)
            ->get("/agent/demandes/{$this->demande->id}");
        $response->assertStatus(200);
        $response->assertViewIs('agent.demandes.show');
    }

    /**
     * Test - Agent ne peut pas voir demandes d'autrui
     */
    public function test_agent_cannot_view_unassigned_demande(): void
    {
        $otherDemande = Demande::factory()->create(['citoyen_id' => $this->citoyen->id]);
        
        $response = $this->actingAs($this->agent)
            ->get("/agent/demandes/{$otherDemande->id}");
        $response->assertStatus(403);
    }

    /**
     * Test - Agent peut accepter une demande
     */
    public function test_agent_can_accept_demande(): void
    {
        $response = $this->actingAs($this->agent)
            ->post("/agent/demandes/{$this->demande->id}/accepter");
        
        $response->assertRedirect();
        $this->demande->refresh();
        $this->assertEquals('acceptee', $this->demande->statut);
    }

    /**
     * Test - Agent peut rejeter une demande
     */
    public function test_agent_can_reject_demande(): void
    {
        $response = $this->actingAs($this->agent)
            ->post("/agent/demandes/{$this->demande->id}/rejeter", [
                'motif_rejet' => 'Documents incomplets'
            ]);
        
        $response->assertRedirect();
        $this->demande->refresh();
        $this->assertEquals('rejetee', $this->demande->statut);
        $this->assertEquals('Documents incomplets', $this->demande->motif_rejet);
    }

    /**
     * Test - Agent non authentifié ne peut pas accéder
     */
    public function test_unauthenticated_user_cannot_access_agent_dashboard(): void
    {
        $response = $this->get('/agent/dashboard');
        $response->assertRedirect('/login');
    }

    /**
     * Test - Citoyen ne peut pas accéder espace agent
     */
    public function test_citoyen_cannot_access_agent_dashboard(): void
    {
        $response = $this->actingAs($this->citoyen)->get('/agent/dashboard');
        $response->assertStatus(403);
    }
}
