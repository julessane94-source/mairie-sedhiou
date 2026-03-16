<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    /**
     * Test la page de connexion
     */
    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    /**
     * Test la page d'inscription
     */
    public function test_register_page_is_accessible(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
    }

    /**
     * Test connexion avec identifiants invalides
     */
    public function test_login_with_invalid_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    /**
     * Test l'inscription d'un nouveau citoyen
     */
    public function test_register_new_citizen(): void
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/citoyen/dashboard');
        $this->assertAuthenticated();
    }

    /**
     * Test la déconnexion
     */
    public function test_logout(): void
    {
        $user = \App\Models\User::factory()->create();
        
        $response = $this->actingAs($user)->post('/logout');
        
        $response->assertRedirect('/');
        $this->assertGuest();
    }
}
