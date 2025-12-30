<?php

namespace Database\Factories;

use App\Models\ApartmentImage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;

class ApartmentImageFactory extends Factory
{
    protected $model = ApartmentImage::class;

    public function definition(): array
    {
        $files = collect(Storage::disk('public')->files('apartment_images'));

        if ($files->isEmpty()) {
            // Fallback if no images exist
            return [
                'image_path' => 'apartment_images/default.jpg',
            ];
        }

        return [
            'image_path' => $files->random(),
        ];
    }
}
