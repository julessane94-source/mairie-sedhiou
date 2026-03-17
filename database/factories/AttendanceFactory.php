<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        return [
            'agent_id' => User::factory()->create(['role' => 'agent'])->id,
            'date_presence' => $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'heure_debut' => $this->faker->optional(0.7)->time(),
            'heure_fin' => $this->faker->optional(0.5)->time(),
            'statut' => $this->faker->randomElement(['present', 'absent', 'retard', 'repos', 'congé']),
            'heures_travaillees' => $this->faker->optional(0.6)->randomFloat(2, 1, 12),
            'notes' => $this->faker->optional(0.3)->sentence(),
            'justificatif' => null,
        ];
    }

    public function present(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'present',
            'heure_debut' => '08:00:00',
            'heure_fin' => '17:00:00',
            'heures_travaillees' => 9.00,
        ]);
    }

    public function absent(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'absent',
            'heure_debut' => null,
            'heure_fin' => null,
        ]);
    }

    public function justified(): static
    {
        return $this->state(fn (array $attributes) => [
            'justifiee' => true,
            'motif' => 'Congé maladie',
        ]);
    }
}
