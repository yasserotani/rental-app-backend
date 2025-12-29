<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Booking;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Booking::factory()->count(10)->create(); // 10 random bookings
        Booking::factory()->count(5)->approved()->create(); // 5 approved bookings
        Booking::factory()->count(3)->pending()->create(); // 3 pending bookings
    }
}
