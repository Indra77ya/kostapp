<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
  $issue = $this->faker->dateTimeBetween('-10 days','now');
  $due = (clone $issue)->modify('+7 days');
  $amount = $this->faker->numberBetween(600000,2000000);
  return [
    'number' => strtoupper($this->faker->bothify('INV-########')),
    'tenant_id' => 1,
    'stay_id' => null,
    'issue_date' => $issue->format('Y-m-d'),
    'due_date' => $due->format('Y-m-d'),
    'subtotal' => $amount,
    'discount' => 0, 'tax' => 0, 'total' => $amount,
    'status' => 'unpaid',
  ];
}

}
