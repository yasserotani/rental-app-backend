<?php

namespace Database\Factories;

use App\Models\Apartment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApartmentFactory extends Factory
{
    protected $model = Apartment::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'address' => $this->faker->address(),
            'description' => $this->faker->sentence(10),
            'city' => $this->faker->city(),
            'governorate' => $this->faker->state(),
            'price' => $this->faker->numberBetween(200, 1500),
            'number_of_rooms' => $this->faker->numberBetween(1, 6), // ✅ ADD THIS
            'is_rented' => false,
        ];
    }
}