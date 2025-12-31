<?php

namespace Database\Factories;

use App\Models\Review;
use App\Models\User;
use App\Models\Apartment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::inRandomOrder()->first() ?? User::factory()->create();
        $apartment = Apartment::inRandomOrder()->first() ?? Apartment::factory()->create();

        return [
            'user_id' => $user->id,
            'apartment_id' => $apartment->id,
            'rating' => $this->faker->numberBetween(1, 5),
            'comment' => $this->faker->optional(0.8)->sentence(10),
        ];
    }
}
