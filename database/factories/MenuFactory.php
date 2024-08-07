<?php

namespace Database\Factories;

use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Menu>
 */
class MenuFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'restaurant_id' => fn() => Restaurant::factory()->create(),
            'name' => fake()->words(nb:3, asText:true),
            'description' => fake()->words(nb:10, asText:true),
            'qr' => 'qr/test.png',
        ];
    }
}
