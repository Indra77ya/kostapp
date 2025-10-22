<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reminder>
 */
class ReminderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
  return [
    'invoice_id' => 1,
    'remind_on' => now()->format('Y-m-d'),
    'channel' => 'wa',
    'status' => $this->faker->randomElement(['pending','sent']),
    'payload' => 'Halo, ini pengingat jatuh tempo sewa.',
  ];
}

}
