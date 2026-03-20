<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    /**
     * Test - Admin peut voir le dashboard
     */
    public function test_admin_can_view_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        $response->assertStatus(200);
    }

    /**
     * Test - Admin peut voir la liste des utilisateurs
     */
    public function test_admin_can_view_users_list(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/utilisateurs');
        $response->assertStatus(200);
    }

    /**
     * Test - Admin peut voir la liste des demandes
     */
    public function test_admin_can_view_demandes_list(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/demandes');
        $response->assertStatus(200);
    }

    /**
     * Test - Citoyen ne peut pas accéder au dashboard admin
     */
    public function test_citoyen_cannot_access_admin_dashboard(): void
    {
        $citoyen = User::factory()->create(['role' => 'citoyen']);
        
        $response = $this->actingAs($citoyen)->get('/admin/dashboard');
        $response->assertStatus(403);
    }
}
