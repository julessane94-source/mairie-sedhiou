<?php

namespace Tests\Unit;

use App\Models\Payment;
use App\Models\User;
use App\Models\Demande;
use App\Services\PaymentReceiptService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentReceiptServiceTest extends TestCase
{
    use RefreshDatabase;

    private PaymentReceiptService $service;
    private User $citoyen;
    private Demande $demande;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PaymentReceiptService::class);
        $this->citoyen = User::factory()->create(['role' => 'citoyen']);
        $this->demande = Demande::factory()->create(['citoyen_id' => $this->citoyen->id]);
    }

    /**
     * Test - Génère référence unique
     */
    public function test_generates_unique_receipt_reference(): void
    {
        $ref1 = $this->service->generateReceiptReference();
        $ref2 = $this->service->generateReceiptReference();

        $this->assertNotEquals($ref1, $ref2);
        $this->assertStringStartsWith('REC-', $ref1);
        $this->assertStringStartsWith('REC-', $ref2);
    }

    /**
     * Test - Crée paiement avec valeurs par défaut
     */
    public function test_creates_payment_with_default_values(): void
    {
        $payment = $this->service->createPayment(
            demandeId: $this->demande->id,
            citoyenId: $this->citoyen->id,
            montant: 50000
        );

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'demande_id' => $this->demande->id,
            'citoyen_id' => $this->citoyen->id,
            'montant' => 50000,
            'methode_paiement' => 'virement',
            'statut' => 'pending',
        ]);
    }

    /**
     * Test - Crée paiement avec tous paramètres
     */
    public function test_creates_payment_with_all_parameters(): void
    {
        $payment = $this->service->createPayment(
            demandeId: $this->demande->id,
            citoyenId: $this->citoyen->id,
            montant: 100000,
            methodePaiement: 'cheque',
            description: 'Paiement certificat'
        );

        $this->assertDatabaseHas('payments', [
            'montant' => 100000,
            'methode_paiement' => 'cheque',
            'description' => 'Paiement certificat',
        ]);
    }

    /**
     * Test - Génère référence unique par paiement
     */
    public function test_each_payment_has_unique_reference(): void
    {
        $payment1 = $this->service->createPayment(
            demandeId: $this->demande->id,
            citoyenId: $this->citoyen->id,
            montant: 50000
        );

        $payment2 = $this->service->createPayment(
            demandeId: $this->demande->id,
            citoyenId: $this->citoyen->id,
            montant: 60000
        );

        $this->assertNotEquals($payment1->reference_recu, $payment2->reference_recu);
    }

    /**
     * Test - Marque paiement comme payé
     */
    public function test_mark_as_paid_updates_status(): void
    {
        $payment = Payment::factory()->create([
            'statut' => 'pending',
            'date_paiement' => null,
        ]);

        $result = $this->service->markAsPaid($payment, 'TXN-123456');

        $this->assertTrue($result->isPaid());
        $this->assertEquals('TXN-123456', $result->numero_transaction);
        $this->assertNotNull($result->date_paiement);
    }

    /**
     * Test - Annule paiement
     */
    public function test_cancel_updates_status(): void
    {
        $payment = Payment::factory()->create(['statut' => 'pending']);

        $result = $this->service->cancel($payment, 'Demande annulée');

        $this->assertEquals('cancelled', $result->statut);
        $this->assertEquals('Demande annulée', $result->description);
    }

    /**
     * Test - Rembourse paiement
     */
    public function test_refund_updates_status(): void
    {
        $payment = Payment::factory()->create(['statut' => 'paid']);

        $result = $this->service->refund($payment, 'Erreur de traitement');

        $this->assertEquals('refunded', $result->statut);
        $this->assertEquals('Erreur de traitement', $result->description);
    }

    /**
     * Test - Format paiement retourne données correctes
     */
    public function test_formatted_payment_info_contains_required_fields(): void
    {
        $payment = Payment::factory()->create([
            'statut' => 'paid',
            'methode_paiement' => 'virement',
            'montant' => 25000.50,
            'devise' => 'XOF',
        ]);

        $formatted = $this->service->getFormattedPaymentInfo($payment);

        $this->assertArrayHasKey('reference', $formatted);
        $this->assertArrayHasKey('montant', $formatted);
        $this->assertArrayHasKey('date_creation', $formatted);
        $this->assertArrayHasKey('statut', $formatted);
        $this->assertArrayHasKey('methode', $formatted);
        $this->assertEquals('Payé', $formatted['statut']);
        $this->assertEquals('Virement bancaire', $formatted['methode']);
    }

    /**
     * Test - Montant formaté avec séparateurs
     */
    public function test_formatted_payment_amount_includes_currency(): void
    {
        $payment = Payment::factory()->create([
            'montant' => 50000,
            'devise' => 'XOF',
        ]);

        $formatted = $this->service->getFormattedPaymentInfo($payment);

        $this->assertStringContainsString('50000', $formatted['montant']);
        $this->assertStringContainsString('XOF', $formatted['montant']);
    }

    /**
     * Test - Référence reçu unique en BD
     */
    public function test_receipt_reference_is_unique_in_database(): void
    {
        $payment1 = $this->service->createPayment(
            demandeId: $this->demande->id,
            citoyenId: $this->citoyen->id,
            montant: 50000
        );

        // Essayer de crée un 2e paiement avec même référence (impossible)
        $payment2 = $this->service->createPayment(
            demandeId: $this->demande->id,
            citoyenId: $this->citoyen->id,
            montant: 60000
        );

        $this->assertNotEquals($payment1->reference_recu, $payment2->reference_recu);
        $this->assertCount(2, Payment::all());
    }
}
