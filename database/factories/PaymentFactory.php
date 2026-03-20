<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Demande;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        $demande = Demande::factory()->create();
        
        return [
            'demande_id' => $demande->id,
            'citoyen_id' => $demande->citoyen_id,
            'montant' => $this->faker->randomFloat(2, 10000, 500000),
            'devise' => 'XOF',
            'methode_paiement' => $this->faker->randomElement(['carte', 'virement', 'especes', 'cheque', 'mobile_money']),
            'statut' => 'pending',
            'numero_transaction' => $this->faker->uuid(),
            'date_paiement' => null,
            'reference_recu' => 'REC-' . $this->faker->unique()->numerify('##########'),
            'description' => $this->faker->sentence(),
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'paid',
            'date_paiement' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'cancelled',
        ]);
    }

    public function refunded(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'refunded',
        ]);
    }
}
