<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentDemandeTest extends TestCase
{
    use RefreshDatabase;

    private User $agent;

    protected function setUp(): void
    {
        parent::setUp();
        $this->agent = User::factory()->create(['role' => 'agent']);
    }

    /**
     * Test - Agent peut voir son dashboard
     */
    public function test_agent_can_view_dashboard(): void
    {
        $response = $this->actingAs($this->agent)->get('/agent/dashboard');
        $response->assertStatus(200);
    }

    /**
     * Test - Agent peut voir les demandes pendantes
     */
    public function test_agent_can_view_pending_demandes(): void
    {
        $response = $this->actingAs($this->agent)->get('/agent/demandes');
        $response->assertStatus(200);
    }

    /**
     * Test - Agent ne peut pas accéder au dashboard admin
     */
    public function test_agent_cannot_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->agent)->get('/admin/dashboard');
        $response->assertStatus(403);
    }
}
