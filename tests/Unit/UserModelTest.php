<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    /**
     * Test - Méthode isAdmin()
     */
    public function test_user_is_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->assertTrue($admin->isAdmin());
    }

    /**
     * Test - Méthode isCitoyen()
     */
    public function test_user_is_citoyen(): void
    {
        $citoyen = User::factory()->create(['role' => 'citoyen']);
        $this->assertTrue($citoyen->isCitoyen());
    }

    /**
     * Test - Méthode isAgent()
     */
    public function test_user_is_agent(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $this->assertTrue($agent->isAgent());
    }

    /**
     * Test - Méthode isActif()
     */
    public function test_user_is_actif(): void
    {
        $user = User::factory()->create(['statut' => 'actif']);
        $this->assertTrue($user->isActif());
    }

    /**
     * Test - Un utilisateur n'est pas admin par défaut
     */
    public function test_user_is_not_admin_by_default(): void
    {
        $user = User::factory()->create();
        $this->assertFalse($user->isAdmin());
    }
}
