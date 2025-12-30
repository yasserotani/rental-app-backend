<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserFactory extends Factory
{
    public function definition(): array
    {
        // Get random profile image from storage
        $profileImages = collect(Storage::disk('public')->files('profile_images'));
        $profileImage = $profileImages->isEmpty()
            ? 'profile_images/default.jpg'
            : $profileImages->random();

        // Get random ID card image from storage
        $idCardImages = collect(Storage::disk('private')->files('id_cards'));
        $idCardImage = $idCardImages->isEmpty()
            ? 'id_cards/default.jpg'
            : $idCardImages->random();

        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'profile_image' => $profileImage,
            'id_card_image' => $idCardImage,
            'phone' => $this->faker->unique()->numerify('09########'),
            'birth_date' => $this->faker->date(),
            'password' => Hash::make('password'), // the hashed password for all users is password
        ];
    }
}
