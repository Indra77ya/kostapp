<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
  $start = $this->faker->dateTimeBetween('-30 days', '+10 days');
  $end = (clone $start)->modify('+3 days');
  return [
    'tenant_id' => 1,
    'room_id' => 1,
    'start_date' => $start->format('Y-m-d'),
    'end_date' => $this->faker->boolean ? $end->format('Y-m-d') : null,
    'status' => $this->faker->randomElement(['pending','confirmed']),
    'rate' => $this->faker->numberBetween(600000,2000000),
  ];
}

}
