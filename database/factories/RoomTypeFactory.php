<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RoomType>
 */
class RoomTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
    return [
      'name' => $this->faker->randomElement(['Standard','Deluxe','Homestay']),
      'billing_cycle' => $this->faker->randomElement(['monthly','daily']),
      'base_price' => $this->faker->numberBetween(500000, 1500000),
    ];
}

}
