<?php

namespace Database\Factories;

use App\Models\ApartmentImage;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApartmentImageFactory extends Factory
{
    protected $model = ApartmentImage::class;

    public function definition(): array
    {
        return [
            'image_path' => 'apartments/' . $this->faker->image('public/storage/apartments', 640, 480, null, false),
        ];
    }
}
