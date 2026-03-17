<?php

namespace Tests\Unit;

use App\Models\Demande;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemandeModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test - Demande acceptée
     */
    public function test_demande_is_acceptee(): void
    {
        $demande = Demande::factory()->create(['statut' => 'acceptee']);
        $this->assertTrue($demande->isAccepte());
    }

    /**
     * Test - Demande rejetée
     */
    public function test_demande_is_rejetee(): void
    {
        $demande = Demande::factory()->create(['statut' => 'rejetee']);
        $this->assertTrue($demande->isRejetee());
    }

    /**
     * Test - Demande pendante
     */
    public function test_demande_is_pendante(): void
    {
        $demande = Demande::factory()->create(['statut' => 'pendante']);
        $this->assertTrue($demande->isPendante());
    }

    /**
     * Test - Relations Demande
     */
    public function test_demande_has_citoyen_relation(): void
    {
        $citoyen = User::factory()->create(['role' => 'citoyen']);
        $demande = Demande::factory()->create(['citoyen_id' => $citoyen->id]);
        
        $this->assertNotNull($demande->citoyen);
        $this->assertEquals($citoyen->id, $demande->citoyen->id);
    }

    /**
     * Test - Demande peut avoir un agent assigné
     */
    public function test_demande_can_have_assigned_agent(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $demande = Demande::factory()->create(['agent_assigne_id' => $agent->id]);
        
        $this->assertNotNull($demande->agentAssigne);
        $this->assertEquals($agent->id, $demande->agentAssigne->id);
    }
}
