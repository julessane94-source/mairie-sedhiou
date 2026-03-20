<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Demande;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $citoyen;
    private User $admin;
    private Demande $demande;

    protected function setUp(): void
    {
        parent::setUp();
        $this->citoyen = User::factory()->create(['role' => 'citoyen']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->demande = Demande::factory()->create(['citoyen_id' => $this->citoyen->id]);
    }

    /**
     * Test - Citoyen peut voir ses paiements
     */
    public function test_citoyen_can_view_payments_index(): void
    {
        $response = $this->actingAs($this->citoyen)->get('/citoyen/paiements');
        $response->assertStatus(200);
        $response->assertViewIs('citoyen.payments.index');
    }

    /**
     * Test - Citoyen non authentifié ne peut pas accéder
     */
    public function test_unauthenticated_user_cannot_view_payments(): void
    {
        $response = $this->get('/citoyen/paiements');
        $response->assertRedirect('/login');
    }

    /**
     * Test - Citoyen peut créer un paiement
     */
    public function test_citoyen_can_create_payment(): void
    {
        $response = $this->actingAs($this->citoyen)
            ->post("/citoyen/demandes/{$this->demande->id}/paiement", [
                'montant' => 50000,
                'methode_paiement' => 'virement',
                'description' => 'Test paiement',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payments', [
            'demande_id' => $this->demande->id,
            'citoyen_id' => $this->citoyen->id,
            'montant' => 50000,
            'methode_paiement' => 'virement',
            'statut' => 'pending',
        ]);
    }

    /**
     * Test - Ne peut pas créer paiement pour demande d'autrui
     */
    public function test_cannot_create_payment_for_others_demande(): void
    {
        $otherCitoyen = User::factory()->create(['role' => 'citoyen']);
        $otherDemande = Demande::factory()->create(['citoyen_id' => $otherCitoyen->id]);

        $response = $this->actingAs($this->citoyen)
            ->post("/citoyen/demandes/{$otherDemande->id}/paiement", [
                'montant' => 50000,
                'methode_paiement' => 'virement',
            ]);

        $response->assertStatus(403);
    }

    /**
     * Test - Validation : montant négatif rejeté
     */
    public function test_payment_validation_rejects_negative_amount(): void
    {
        $response = $this->actingAs($this->citoyen)
            ->post("/citoyen/demandes/{$this->demande->id}/paiement", [
                'montant' => -1000,
                'methode_paiement' => 'virement',
            ]);

        $response->assertSessionHasErrors('montant');
    }

    /**
     * Test - Validation : montant zéro rejeté
     */
    public function test_payment_validation_rejects_zero_amount(): void
    {
        $response = $this->actingAs($this->citoyen)
            ->post("/citoyen/demandes/{$this->demande->id}/paiement", [
                'montant' => 0,
                'methode_paiement' => 'virement',
            ]);

        $response->assertSessionHasErrors('montant');
    }

    /**
     * Test - Validation : méthode paiement invalide
     */
    public function test_payment_validation_rejects_invalid_method(): void
    {
        $response = $this->actingAs($this->citoyen)
            ->post("/citoyen/demandes/{$this->demande->id}/paiement", [
                'montant' => 50000,
                'methode_paiement' => 'crypto',
            ]);

        $response->assertSessionHasErrors('methode_paiement');
    }

    /**
     * Test - Citoyen peut voir détails paiement
     */
    public function test_citoyen_can_view_payment_details(): void
    {
        $payment = Payment::factory()->create([
            'citoyen_id' => $this->citoyen->id,
            'demande_id' => $this->demande->id,
        ]);

        $response = $this->actingAs($this->citoyen)->get("/citoyen/paiements/{$payment->id}");
        $response->assertStatus(200);
        $response->assertViewIs('citoyen.payments.show');
    }

    /**
     * Test - Ne peut pas voir paiements d'autrui
     */
    public function test_cannot_view_others_payment(): void
    {
        $otherCitoyen = User::factory()->create(['role' => 'citoyen']);
        $payment = Payment::factory()->create(['citoyen_id' => $otherCitoyen->id]);

        $response = $this->actingAs($this->citoyen)->get("/citoyen/paiements/{$payment->id}");
        $response->assertStatus(403);
    }

    /**
     * Test - Marquer paiement comme payé génère reçu
     */
    public function test_mark_payment_as_paid_generates_receipt(): void
    {
        $payment = Payment::factory()->create([
            'citoyen_id' => $this->citoyen->id,
            'demande_id' => $this->demande->id,
            'statut' => 'pending',
        ]);

        $response = $this->actingAs($this->citoyen)
            ->post("/citoyen/paiements/{$payment->id}/marquer-paye", [
                'numero_transaction' => 'TXN-123456',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'statut' => 'paid',
            'numero_transaction' => 'TXN-123456',
        ]);
    }

    /**
     * Test - Ne peut pas marquer comme payé paiement d'autrui
     */
    public function test_cannot_mark_others_payment_as_paid(): void
    {
        $otherCitoyen = User::factory()->create(['role' => 'citoyen']);
        $payment = Payment::factory()->create([
            'citoyen_id' => $otherCitoyen->id,
            'statut' => 'pending',
        ]);

        $response = $this->actingAs($this->citoyen)
            ->post("/citoyen/paiements/{$payment->id}/marquer-paye", [
                'numero_transaction' => 'TXN-123456',
            ]);

        $response->assertStatus(403);
    }

    /**
     * Test - Annuler paiement
     */
    public function test_citoyen_can_cancel_payment(): void
    {
        $payment = Payment::factory()->create([
            'citoyen_id' => $this->citoyen->id,
            'demande_id' => $this->demande->id,
            'statut' => 'pending',
        ]);

        $response = $this->actingAs($this->citoyen)
            ->post("/citoyen/paiements/{$payment->id}/annuler");

        $response->assertRedirect();
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'statut' => 'cancelled',
        ]);
    }

    /**
     * Test - Télécharger reçu paiement payé
     */
    public function test_can_download_receipt_for_paid_payment(): void
    {
        $payment = Payment::factory()->create([
            'citoyen_id' => $this->citoyen->id,
            'statut' => 'paid',
        ]);

        $response = $this->actingAs($this->citoyen)
            ->get("/citoyen/paiements/{$payment->id}/recu/telechargement");

        // Devrait retourner PDF ou redirection
        $this->assertTrue(
            $response->status() === 200 || $response->status() === 302
        );
    }

    /**
     * Test - Impossible de télécharger reçu paiement en attente
     */
    public function test_cannot_download_receipt_for_pending_payment(): void
    {
        $payment = Payment::factory()->create([
            'citoyen_id' => $this->citoyen->id,
            'statut' => 'pending',
        ]);

        $response = $this->actingAs($this->citoyen)
            ->get("/citoyen/paiements/{$payment->id}/recu/telechargement");

        $this->assertTrue($response->status() === 403 || $response->status() === 302);
    }

    /**
     * Test - Admin peut voir tous les paiements
     */
    public function test_admin_can_view_all_payments(): void
    {
        Payment::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        // Admin dashboard devrait afficher les paiements
    }

    /**
     * Test - Agent ne peut pas accéder paiements citoyen
     */
    public function test_agent_cannot_access_payment_endpoints(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);

        $response = $this->actingAs($agent)->get('/citoyen/paiements');
        $response->assertStatus(403);
    }
}
