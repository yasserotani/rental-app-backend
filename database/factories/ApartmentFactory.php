<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Apartment>
 */
class ApartmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id, // pick a random user id
            'address' => fake()->address(),
            'description' => fake()->realText(), // وصف الشقة
            'governorate' => fake()->state(),
            'city' => fake()->city(),
            'number_of_rooms' => fake()->numberBetween(1, 6),
            'price' => fake()->randomFloat(2, 10000, 500000), // السعر
            'is_rented' => fake()->boolean(30), // احتمال 30% أن تكون مؤجرة
        ];
    }
}