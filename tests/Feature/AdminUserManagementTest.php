<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    /**
     * Test - Admin peut créer un nouvel utilisateur
     */
    public function test_admin_can_create_user(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/utilisateurs', [
            'name' => 'Jean Dupont',
            'email' => 'jean.dupont@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'role' => 'citoyen',
            'statut' => 'actif',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'name' => 'Jean Dupont',
            'email' => 'jean.dupont@example.com',
            'role' => 'citoyen',
            'statut' => 'actif',
        ]);
    }

    /**
     * Test - Admin peut éditer un utilisateur
     */
    public function test_admin_can_edit_user(): void
    {
        $user = User::factory()->create(['name' => 'Old Name']);
        
        $response = $this->actingAs($this->admin)
            ->patch("/admin/utilisateurs/{$user->id}", [
                'name' => 'New Name',
                'email' => $user->email,
                'role' => $user->role,
                'statut' => $user->statut,
            ]);
        
        $response->assertRedirect();
        $user->refresh();
        $this->assertEquals('New Name', $user->name);
    }

    /**
     * Test - Admin peut changer le rôle d'un utilisateur
     */
    public function test_admin_can_change_user_role(): void
    {
        $user = User::factory()->create(['role' => 'citoyen']);
        
        $response = $this->actingAs($this->admin)
            ->patch("/admin/utilisateurs/{$user->id}/role", ['role' => 'agent']);
        
        $response->assertRedirect();
        $user->refresh();
        $this->assertEquals('agent', $user->role);
    }

    /**
     * Test - Admin peut changer le statut d'un utilisateur
     */
    public function test_admin_can_change_user_status(): void
    {
        $user = User::factory()->create(['statut' => 'actif']);
        
        $response = $this->actingAs($this->admin)
            ->patch("/admin/utilisateurs/{$user->id}/statut", ['statut' => 'suspendu']);
        
        $response->assertRedirect();
        $user->refresh();
        $this->assertEquals('suspendu', $user->statut);
    }

    /**
     * Test - Admin peut supprimer un utilisateur (sauf le dernier admin)
     */
    public function test_admin_can_delete_user(): void
    {
        $user = User::factory()->create(['role' => 'citoyen']);
        
        $response = $this->actingAs($this->admin)
            ->delete("/admin/utilisateurs/{$user->id}");
        
        $response->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    /**
     * Test - Ne peut pas supprimer le dernier admin
     */
    public function test_cannot_delete_last_admin(): void
    {
        // Créer un seul admin
        $lastAdmin = User::factory()->create(['role' => 'admin']);
        
        $response = $this->actingAs($lastAdmin)
            ->delete("/admin/utilisateurs/{$lastAdmin->id}");
        
        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $lastAdmin->id]);
    }

    /**
     * Test - Validation : email unique
     */
    public function test_cannot_create_user_with_duplicate_email(): void
    {
        $existingUser = User::factory()->create();
        
        $response = $this->actingAs($this->admin)->post('/admin/utilisateurs', [
            'name' => 'Another User',
            'email' => $existingUser->email,
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'role' => 'citoyen',
            'statut' => 'actif',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Test - Validation : password minimum 8 caractères
     */
    public function test_password_must_be_minimum_8_characters(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/utilisateurs', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'pass',
            'password_confirmation' => 'pass',
            'role' => 'citoyen',
            'statut' => 'actif',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * Test - Validation : role valide
     */
    public function test_role_must_be_valid(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/utilisateurs', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'role' => 'invalid_role',
            'statut' => 'actif',
        ]);

        $response->assertSessionHasErrors('role');
    }
}
