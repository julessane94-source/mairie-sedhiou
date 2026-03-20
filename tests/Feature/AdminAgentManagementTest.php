<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Demande;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAgentManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    /**
     * Test - Admin peut voir la liste des agents
     */
    public function test_admin_can_view_agents_list(): void
    {
        User::factory()->create(['role' => 'agent']);
        
        $response = $this->actingAs($this->admin)->get('/admin/agents');
        $response->assertStatus(200);
        $response->assertViewIs('admin.agents.index');
    }

    /**
     * Test - Admin peut voir les détails d'un agent
     */
    public function test_admin_can_view_agent_details(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        
        $response = $this->actingAs($this->admin)->get("/admin/agents/{$agent->id}");
        $response->assertStatus(200);
        $response->assertViewIs('admin.agents.show');
    }

    /**
     * Test - Admin peut créer un agent
     */
    public function test_admin_can_create_agent(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/agents', [
            'nom' => 'Sow',
            'prenom' => 'Abdoulaye',
            'email' => 'abdoulaye.sow@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'role' => 'agent',
            'statut' => 'actif',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email' => 'abdoulaye.sow@example.com',
            'role' => 'agent',
        ]);
    }

    /**
     * Test - Admin peut éditer un agent
     */
    public function test_admin_can_edit_agent(): void
    {
        $agent = User::factory()->create(['role' => 'agent', 'name' => 'John Doe']);
        
        $response = $this->actingAs($this->admin)->get("/admin/agents/{$agent->id}/edit");
        $response->assertStatus(200);
        $response->assertViewIs('admin.agents.edit');
    }

    /**
     * Test - Admin peut changer le statut d'un agent
     */
    public function test_admin_can_change_agent_status(): void
    {
        $agent = User::factory()->create(['role' => 'agent', 'statut' => 'actif']);
        
        $response = $this->actingAs($this->admin)
            ->patch("/admin/agents/{$agent->id}/statut", ['statut' => 'inactif']);
        
        $response->assertRedirect();
        $agent->refresh();
        $this->assertEquals('inactif', $agent->statut);
    }

    /**
     * Test - Admin peut assigner une demande à un agent
     */
    public function test_admin_can_assign_demande_to_agent(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $citoyen = User::factory()->create(['role' => 'citoyen']);
        $demande = Demande::factory()->create(['citoyen_id' => $citoyen->id]);
        
        $response = $this->actingAs($this->admin)
            ->post("/admin/agents/{$agent->id}/assigner-demande", [
                'demande_id' => $demande->id
            ]);
        
        $response->assertRedirect();
        $demande->refresh();
        $this->assertEquals($agent->id, $demande->agent_assigne_id);
    }

    /**
     * Test - Citoyen non authentifié ne peut pas voir agents
     */
    public function test_unauthenticated_cannot_view_agents(): void
    {
        $response = $this->get('/admin/agents');
        $response->assertRedirect('/login');
    }

    /**
     * Test - Citoyen ne peut pas gérer les agents
     */
    public function test_citoyen_cannot_manage_agents(): void
    {
        $citoyen = User::factory()->create(['role' => 'citoyen']);
        
        $response = $this->actingAs($citoyen)->get('/admin/agents');
        $response->assertStatus(403);
    }
}
