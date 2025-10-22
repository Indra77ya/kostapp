<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
  return [
    'invoice_id' => 1,
    'paid_at' => now()->format('Y-m-d'),
    'amount' => $this->faker->numberBetween(200000,1000000),
    'method' => $this->faker->randomElement(['cash','transfer','qris']),
    'reference' => strtoupper($this->faker->bothify('PAY-####')),
  ];
}

}
