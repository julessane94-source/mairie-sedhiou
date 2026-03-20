<?php

namespace Database\Factories;

use App\Models\Demande;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Demande>
 */
class DemandeFactory extends Factory
{
    protected $model = Demande::class;

    public function definition(): array
    {
        return [
            'citoyen_id' => User::factory()->create(['role' => 'citoyen'])->id,
            'titre' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'type' => $this->faker->randomElement(['Certificat', 'Autorisation', 'Document', 'Permis']),
            'statut' => 'pendante',
            'priorite' => $this->faker->randomElement(['basse', 'normale', 'haute', 'urgente']),
            'agent_assigne_id' => null,
            'date_limite' => $this->faker->dateTimeBetween('+1 day', '+30 days'),
            'motif_rejet' => null,
        ];
    }

    public function acceptee(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'acceptee',
        ]);
    }

    public function rejetee(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'rejetee',
            'motif_rejet' => 'Documents incomplets',
        ]);
    }

    public function enCours(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'en_cours',
            'agent_assigne_id' => User::factory()->create(['role' => 'agent'])->id,
        ]);
    }
}
