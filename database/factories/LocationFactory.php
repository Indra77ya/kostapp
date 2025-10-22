<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Location>
 */
class LocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
    return [
        'name' => 'Kost ' . $this->faker->streetName(),
        'code' => strtoupper($this->faker->bothify('KST-##')),
        'address' => $this->faker->address(),
        'type' => 'kost',
        'default_room_quota' => 40,
        'wa_group_link' => $this->faker->optional()->url(),
    ];
}
}
