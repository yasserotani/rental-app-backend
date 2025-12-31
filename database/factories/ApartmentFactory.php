<?php

namespace Database\Factories;

use App\Models\Apartment;
use App\Models\ApartmentImage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApartmentFactory extends Factory
{
    protected $model = Apartment::class;

    public function definition(): array
    {
        $governorates = ['دمشق', 'ريف دمشق', 'حمص', 'حلب'];
        $cities = [
            'دمشق' => ['المزة', 'المالكي', 'برزة'],
            'ريف دمشق' => ['جرمانا', 'قدسيا', 'دوما'],
            'حمص' => ['الوعر', 'الزهراء'],
            'حلب' => ['الحمدانية', 'السكري'],
        ];

        $gov = $this->faker->randomElement($governorates);
        $city = $this->faker->randomElement($cities[$gov]);

        return [
            'user_id' => User::factory(),
            'address' => $this->faker->streetAddress(),
            'description' => $this->faker->sentence(10),
            'city' => $city,
            'governorate' => $gov,
            'price' => $this->faker->numberBetween(1000, int2: 10000),
            'number_of_rooms' => $this->faker->numberBetween(1, 6),
            'is_rented' => $this->faker->boolean(30),
        ];
    }

    /**
     * Configure the factory to automatically create apartment images.
     */
    public function configure()
    {
        return $this->afterCreating(function (Apartment $apartment) {
            // Create 2-5 images for each apartment
            $imageCount = rand(2, 5);
            ApartmentImage::factory()
                ->count($imageCount)
                ->for($apartment)
                ->create();
        });
    }
}
