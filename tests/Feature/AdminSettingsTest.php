<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\PlatformSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    /**
     * Test - Admin peut voir les paramètres
     */
    public function test_admin_can_view_settings(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/parametres');
        $response->assertStatus(200);
        $response->assertViewIs('admin.settings.index');
    }

    /**
     * Test - Admin peut voir paramètres application
     */
    public function test_admin_can_view_application_settings(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/parametres/application');
        $response->assertStatus(200);
        $response->assertViewIs('admin.settings.application');
    }

    /**
     * Test - Admin peut mettre à jour paramètres application
     */
    public function test_admin_can_update_application_settings(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/parametres/application', [
                'app_name' => 'MAIRI Mise à jour',
                'app_email' => 'contact@mairi.sn',
            ]);
        
        $response->assertRedirect();
    }

    /**
     * Test - Admin peut voir paramètres opérations
     */
    public function test_admin_can_view_operations_settings(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/parametres/operations');
        $response->assertStatus(200);
        $response->assertViewIs('admin.settings.operations');
    }

    /**
     * Test - Admin peut voir paramètres sécurité
     */
    public function test_admin_can_view_security_settings(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/parametres/securite');
        $response->assertStatus(200);
        $response->assertViewIs('admin.settings.security');
    }

    /**
     * Test - Admin peut voir paramètres notifications
     */
    public function test_admin_can_view_notifications_settings(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/parametres/notifications');
        $response->assertStatus(200);
        $response->assertViewIs('admin.settings.notifications');
    }

    /**
     * Test - Admin peut voir les logs
     */
    public function test_admin_can_view_logs(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/parametres/logs');
        $response->assertStatus(200);
        $response->assertViewIs('admin.settings.logs');
    }

    /**
     * Test - Admin peut effacer les logs
     */
    public function test_admin_can_clear_logs(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/parametres/logs/effacer');
        
        $response->assertRedirect();
    }

    /**
     * Test - Citoyen ne peut pas accéder aux paramètres
     */
    public function test_citoyen_cannot_access_settings(): void
    {
        $citoyen = User::factory()->create(['role' => 'citoyen']);
        
        $response = $this->actingAs($citoyen)->get('/admin/parametres');
        $response->assertStatus(403);
    }
}
