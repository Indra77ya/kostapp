<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Room>
 */
class RoomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
    return [
      'location_id' => 1, // override di seeder
      'room_type_id' => 1, // override di seeder
      'number' => strtoupper($this->faker->bothify('A-###')),
      'status' => 'available',
      'floor' => $this->faker->numberBetween(1,3),
      'amenities' => ['AC','WiFi'],
    ];
}

}
