<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Apartment;
use Illuminate\Database\Eloquent\Factories\Factory;


class ApartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
  public function run(): void
    {
        Apartment::factory()
            ->count(20)
            ->hasImages(3)
            ->create();
    }
}