<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'profile_image' => 'profile_images/' . $this->faker->unique()->numerify('user####.jpg'),
            'id_card_image' => 'id_cards/' . $this->faker->unique()->numerify('id####.jpg'),
            'phone' => $this->faker->unique()->numerify('09########'),
            'birth_date' => $this->faker->date(),
            'password' => Hash::make('password123'), // the hashed password for all users is password123
        ];
    }
}