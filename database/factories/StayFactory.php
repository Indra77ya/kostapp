<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Stay>
 */
class StayFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
  $checkin = $this->faker->dateTimeBetween('-20 days','now');
  return [
    'tenant_id' => 1, 'room_id' => 1,
    'checkin_date' => $checkin->format('Y-m-d'),
    'checkout_date' => null,
    'billing_cycle' => $this->faker->randomElement(['monthly','daily']),
    'rate' => $this->faker->numberBetween(600000,2000000),
    'status' => 'active',
  ];
}

}
