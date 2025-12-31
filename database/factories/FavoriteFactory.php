<?php

namespace Database\Factories;

use App\Models\Favorites;
use App\Models\User;
use App\Models\Apartment;
use App\Models\Favorite;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Favorites>
 */
class FavoriteFactory extends Factory
{
    protected $model = Favorite::class;

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
        ];
    }
}
