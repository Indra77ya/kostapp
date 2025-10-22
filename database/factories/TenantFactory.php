<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenant>
 */
class TenantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
  return [
    'full_name' => $this->faker->name(),
    'phone' => $this->faker->phoneNumber(),
    'email' => $this->faker->unique()->safeEmail(),
    'national_id' => $this->faker->optional()->numerify('################'),
    'notes' => $this->faker->optional()->sentence(),
  ];
}

}
