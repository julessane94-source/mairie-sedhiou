<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Demande;
use App\Models\ProfilCitoyen;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CitoyenProfilTest extends TestCase
{
    use RefreshDatabase;

    private User $citoyen;

    protected function setUp(): void
    {
        parent::setUp();
        $this->citoyen = User::factory()->create(['role' => 'citoyen']);
    }

    /**
     * Test - Citoyen peut voir son profil
     */
    public function test_citoyen_can_view_profile(): void
    {
        $response = $this->actingAs($this->citoyen)->get('/citoyen/profil/edit');
        $response->assertStatus(200);
        $response->assertViewIs('citoyen.profil.edit');
    }

    /**
     * Test - Citoyen peut mettre à jour son profil
     */
    public function test_citoyen_can_update_profile(): void
    {
        $response = $this->actingAs($this->citoyen)->patch('/citoyen/profil', [
            'telephone' => '+221771234567',
            'adresse' => '123 Rue de la Paix, Dakar',
            'ville' => 'Dakar',
        ]);

        $response->assertRedirect();
    }

    /**
     * Test - Citoyen non authentifié ne peut pas voir profil
     */
    public function test_unauthenticated_cannot_view_profile(): void
    {
        $response = $this->get('/citoyen/profil/edit');
        $response->assertRedirect('/login');
    }

    /**
     * Test - Agent ne peut pas modifier profil citoyen
     */
    public function test_agent_cannot_modify_citizen_profile(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        
        $response = $this->actingAs($agent)->patch('/citoyen/profil', [
            'telephone' => '+221771234567',
        ]);

        $response->assertStatus(403);
    }
}
