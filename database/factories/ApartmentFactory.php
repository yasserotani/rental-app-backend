<?php

namespace Database\Factories;

use App\Models\Apartment;
use App\Models\ApartmentImage;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

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
            'title' => $this->faker->sentence(3),
            'address' => $this->faker->streetAddress(),
            'description' => $this->faker->sentence(10),
            'governorate' => $gov,
            'city' => $city,
            'number_of_rooms' => $this->faker->numberBetween(1, 6),
            'area' => $this->faker->randomFloat(2, 50, 300),
            'price' => $this->faker->numberBetween(1000, 10000),
            'is_rented' => false, // Will be set based on approved bookings
            'average_rating' => $this->faker->randomFloat(2, 0, 5),
            'reviews_count' => $this->faker->numberBetween(0, 100),
        ];
    }

    /**
     * Configure the factory to automatically create apartment images, bookings, and set is_rented.
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

            // Create 0-3 bookings for the apartment
            $bookingCount = rand(0, 3);
            $today = Carbon::today();
            $hasActiveApprovedBooking = false;

            for ($i = 0; $i < $bookingCount; $i++) {
                // 30% chance to create a currently active approved booking
                if ($this->faker->boolean(30) && !$hasActiveApprovedBooking) {
                    // Create a currently active approved booking
                    $startDate = $this->faker->dateTimeBetween('-30 days', 'now');
                    $start = Carbon::instance($startDate);
                    $endDate = $this->faker->dateTimeBetween('now', '+90 days');
                    $end = Carbon::instance($endDate);

                    // Ensure the booking overlaps with today
                    if ($start->gt($today)) {
                        $start = $today->copy();
                    }
                    if ($end->lt($today)) {
                        $end = $today->copy()->addDays(rand(1, 30));
                    }

                    $days = $start->diffInDays($end) + 1;
                    $totalPrice = round($days * $apartment->price, 2);

                    Booking::create([
                        'user_id' => User::factory()->create()->id,
                        'apartment_id' => $apartment->id,
                        'start_date' => $start->format('Y-m-d'),
                        'end_date' => $end->format('Y-m-d'),
                        'status' => 'approved',
                        'total_price' => $totalPrice,
                    ]);

                    $hasActiveApprovedBooking = true;
                } else {
                    // Create a regular booking (past or future)
                    Booking::factory()
                        ->for($apartment)
                        ->create();
                }
            }

            // Update is_rented based on active approved bookings
            if ($hasActiveApprovedBooking) {
                $apartment->update(['is_rented' => true]);
            } else {
                // Double-check if there's an approved booking at the current time
                $hasActiveBooking = Booking::where('apartment_id', $apartment->id)
                    ->where('status', 'approved')
                    ->where('start_date', '<=', $today)
                    ->where('end_date', '>=', $today)
                    ->exists();

                if ($hasActiveBooking) {
                    $apartment->update(['is_rented' => true]);
                }
            }
        });
    }
}
