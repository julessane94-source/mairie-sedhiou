<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterCitizenTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test - Inscription réussie avec tous les champs
     */
    public function test_citizen_can_register_with_all_fields(): void
    {
        $response = $this->post('/register', [
            'prenom' => 'Jean',
            'nom' => 'Dupont',
            'email' => 'jean.dupont@example.com',
            'date_naissance' => '1990-05-15',
            'lieu_naissance' => 'Dakar',
            'numero_registre' => 'SN-2024-123456',
            'adresse' => '123 rue des Fleurs, Dakar, Sénégal',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $response->assertRedirect('/citoyen/dashboard');
        $this->assertAuthenticated();

        $user = User::where('email', 'jean.dupont@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Jean', $user->prenom);
        $this->assertEquals('Dupont', $user->nom);
        $this->assertNotNull($user->numero_citoyen);
    }

    /**
     * Test - Numéro de citoyen généré correctement
     */
    public function test_citizen_number_generated_on_registration(): void
    {
        $this->post('/register', [
            'prenom' => 'Marie',
            'nom' => 'Martin',
            'email' => 'marie.martin@example.com',
            'date_naissance' => '1985-03-20',
            'lieu_naissance' => 'Saint-Louis',
            'numero_registre' => 'SN-2024-654321',
            'adresse' => '456 avenue principale, Saint-Louis',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $user = User::where('email', 'marie.martin@example.com')->first();

        // Le numéro de citoyen doit être généré
        $this->assertNotNull($user->numero_citoyen);
        // Format : YYYYMMDD-REGISTRE-CHECKSUM
        $this->assertMatchesRegularExpression(
            '/^\d{8}-\d+-[A-Z0-9]{3}$/',
            $user->numero_citoyen
        );
        // Doit contenir la date de naissance
        $this->assertStringStartsWith('19850320', $user->numero_citoyen);
    }

    /**
     * Test - Profil créé avec données complètes
     */
    public function test_profile_created_with_registration_data(): void
    {
        $this->post('/register', [
            'prenom' => 'Ahmed',
            'nom' => 'Sy',
            'email' => 'ahmed.sy@example.com',
            'date_naissance' => '1995-12-10',
            'lieu_naissance' => 'Kaolack',
            'numero_registre' => 'SN-2024-789012',
            'adresse' => '789 boulevard central, Kaolack',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $user = User::where('email', 'ahmed.sy@example.com')->first();
        $profil = $user->profil;

        $this->assertNotNull($profil);
        $this->assertEquals('1995-12-10', $profil->date_naissance);
        $this->assertEquals('Kaolack', $profil->lieu_naissance);
        $this->assertEquals('SN-2024-789012', $profil->numero_registre);
        $this->assertEquals('789 boulevard central, Kaolack', $profil->adresse);
    }

    /**
     * Test - Email unique requis
     */
    public function test_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'existe@example.com']);

        $response = $this->post('/register', [
            'prenom' => 'Test',
            'nom' => 'User',
            'email' => 'existe@example.com',
            'date_naissance' => '1990-01-01',
            'lieu_naissance' => 'Test City',
            'numero_registre' => 'TEST-000001',
            'adresse' => '123 Test Street',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Test - Numéro registre unique requis
     */
    public function test_registre_number_must_be_unique(): void
    {
        $user = User::factory()->create();
        $user->profil()->update(['numero_registre' => 'UNIQUE-123456']);

        $response = $this->post('/register', [
            'prenom' => 'Test',
            'nom' => 'User',
            'email' => 'test@example.com',
            'date_naissance' => '1990-01-01',
            'lieu_naissance' => 'Test City',
            'numero_registre' => 'UNIQUE-123456',
            'adresse' => '123 Test Street',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $response->assertSessionHasErrors('numero_registre');
    }

    /**
     * Test - Validation : prénom requis
     */
    public function test_prenom_is_required(): void
    {
        $response = $this->post('/register', [
            'nom' => 'Dupont',
            'email' => 'test@example.com',
            'date_naissance' => '1990-01-01',
            'lieu_naissance' => 'Test City',
            'numero_registre' => 'TEST-123456',
            'adresse' => '123 Test Street',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $response->assertSessionHasErrors('prenom');
    }

    /**
     * Test - Validation : date de naissance ne peut pas être future
     */
    public function test_date_naissance_cannot_be_future(): void
    {
        $futureDate = now()->addMonth()->format('Y-m-d');

        $response = $this->post('/register', [
            'prenom' => 'Test',
            'nom' => 'User',
            'email' => 'test@example.com',
            'date_naissance' => $futureDate,
            'lieu_naissance' => 'Test City',
            'numero_registre' => 'TEST-123456',
            'adresse' => '123 Test Street',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $response->assertSessionHasErrors('date_naissance');
    }

    /**
     * Test - Validation : mot de passe fort
     */
    public function test_password_must_be_strong(): void
    {
        $response = $this->post('/register', [
            'prenom' => 'Test',
            'nom' => 'User',
            'email' => 'test@example.com',
            'date_naissance' => '1990-01-01',
            'lieu_naissance' => 'Test City',
            'numero_registre' => 'TEST-123456',
            'adresse' => '123 Test Street',
            'password' => 'weak',  // Pas assez fort
            'password_confirmation' => 'weak',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * Test - Confirmation mot de passe
     */
    public function test_password_confirmation_required(): void
    {
        $response = $this->post('/register', [
            'prenom' => 'Test',
            'nom' => 'User',
            'email' => 'test@example.com',
            'date_naissance' => '1990-01-01',
            'lieu_naissance' => 'Test City',
            'numero_registre' => 'TEST-123456',
            'adresse' => '123 Test Street',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'DifferentPass123!',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * Test - Citoyen a le bon rôle après inscription
     */
    public function test_registered_user_has_citoyen_role(): void
    {
        $this->post('/register', [
            'prenom' => 'Test',
            'nom' => 'Citoyen',
            'email' => 'test.citoyen@example.com',
            'date_naissance' => '1990-01-01',
            'lieu_naissance' => 'Test City',
            'numero_registre' => 'TEST-111111',
            'adresse' => '123 Test Street',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $user = User::where('email', 'test.citoyen@example.com')->first();
        $this->assertTrue($user->isCitoyen());
        $this->assertEquals('actif', $user->statut);
    }
}
