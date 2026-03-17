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
            'date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'statut' => $this->faker->randomElement(['present', 'absent']),
            'check_in' => $this->faker->optional(0.7)->time(),
            'check_out' => $this->faker->optional(0.5)->time(),
            'justifiee' => false,
            'motif' => null,
        ];
    }

    public function present(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'present',
            'check_in' => '08:00:00',
            'check_out' => '17:00:00',
        ]);
    }

    public function absent(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'absent',
            'check_in' => null,
            'check_out' => null,
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
