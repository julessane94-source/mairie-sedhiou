<?php

namespace Database\Factories;

use App\Models\Profil;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Profil>
 */
class ProfilFactory extends Factory
{
    protected $model = Profil::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->create(['role' => 'citoyen'])->id,
            'date_naissance' => $this->faker->dateTimeBetween('-60 years', '-18 years'),
            'lieu_naissance' => $this->faker->city(),
            'numero_registre' => $this->faker->unique()->numerify('SN-###-######'),
            'adresse' => $this->faker->address(),
            'telephone' => $this->faker->phoneNumber(),
            'ville' => $this->faker->city(),
            'code_postal' => $this->faker->postcode(),
        ];
    }
}
