<?php

namespace Tests\Unit;

use App\Models\Payment;
use App\Models\User;
use App\Models\Demande;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test - Paiement en attente
     */
    public function test_payment_is_pending(): void
    {
        $payment = Payment::factory()->create(['statut' => 'pending']);
        $this->assertTrue($payment->isPending());
    }

    /**
     * Test - Paiement payé
     */
    public function test_payment_is_paid(): void
    {
        $payment = Payment::factory()->create(['statut' => 'paid']);
        $this->assertTrue($payment->isPaid());
    }

    /**
     * Test - Relations Payment
     */
    public function test_payment_has_citoyen_relation(): void
    {
        $citoyen = User::factory()->create(['role' => 'citoyen']);
        $payment = Payment::factory()->create(['citoyen_id' => $citoyen->id]);
        
        $this->assertNotNull($payment->citoyen);
        $this->assertEquals($citoyen->id, $payment->citoyen->id);
    }

    /**
     * Test - Paiement associé à une demande
     */
    public function test_payment_belongs_to_demande(): void
    {
        $citoyen = User::factory()->create(['role' => 'citoyen']);
        $demande = Demande::factory()->create(['citoyen_id' => $citoyen->id]);
        $payment = Payment::factory()->create(['demande_id' => $demande->id]);
        
        $this->assertNotNull($payment->demande);
        $this->assertEquals($demande->id, $payment->demande->id);
    }
}
