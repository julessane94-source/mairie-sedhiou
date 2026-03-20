<?php

namespace Database\Factories;

use App\Models\Message;
use App\Models\Demande;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition(): array
    {
        $demande = Demande::factory()->create();
        
        return [
            'demande_id' => $demande->id,
            'expediteur_id' => $this->faker->randomElement([$demande->citoyen_id, $demande->agent_assigne_id ?? User::factory()->create(['role' => 'agent'])->id]),
            'contenu' => $this->faker->paragraph(),
            'lu' => $this->faker->boolean(),
        ];
    }

    public function fromCitoyen(): static
    {
        return $this->state(function (array $attributes) {
            $demande = Demande::find($attributes['demande_id']) ?? Demande::factory()->create();
            return [
                'demande_id' => $demande->id,
                'expediteur_id' => $demande->citoyen_id,
            ];
        });
    }

    public function fromAgent(): static
    {
        return $this->state(function (array $attributes) {
            $demande = Demande::find($attributes['demande_id']) ?? Demande::factory()->create();
            return [
                'demande_id' => $demande->id,
                'expediteur_id' => $demande->agent_assigne_id ?? User::factory()->create(['role' => 'agent'])->id,
            ];
        });
    }
}
