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
            'price' => $this->faker->numberBetween(300000, 1500000),
            'number_of_rooms' => $this->faker->numberBetween(1, 6),
            'is_rented' => $this->faker->boolean(30),
        ];
    }
}
