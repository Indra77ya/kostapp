<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
  return [
    'tenant_id' => 1,
    'room_id' => 1,
    'subject' => $this->faker->randomElement(['Keran bocor','AC tidak dingin','Lampu mati']),
    'description' => $this->faker->sentence(),
    'priority' => $this->faker->randomElement(['low','medium','high']),
    'status' => $this->faker->randomElement(['open','in_progress','resolved']),
    'assigned_to' => null,
  ];
}

}
