<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\User;
use App\Models\Apartment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition()
    {
        $user = User::inRandomOrder()->first() ?? User::factory()->create();
        $apartment = Apartment::inRandomOrder()->first() ?? Apartment::factory()->create();

        $startDate = $this->faker->dateTimeBetween('now', '+30 days');
        $endDate = (clone $startDate)->modify('+' . rand(1, 7) . ' days');

        $startDateCarbon = Carbon::instance($startDate);
        $endDateCarbon = Carbon::instance($endDate);

        // get the total price
        $days = $startDateCarbon->diffInDays($endDateCarbon) + 1;
        $totalPrice = $days * $apartment->price;

        $statuses = ['pending', 'approved', 'rejected', 'cancelled'];
        $status = $this->faker->randomElement($statuses);

        return [
            'user_id' => $user->id,
            'apartment_id' => $apartment->id,
            'start_date' => $startDateCarbon->format('Y-m-d'),
            'end_date' => $endDateCarbon->format('Y-m-d'),
            'status' => $status,
            'total_price' => round($totalPrice, 2),
        ];
    }

    public function approved()
    {
        return $this->state(function (array $attributes) {
            return ['status' => 'approved'];
        });
    }

    public function pending()
    {
        return $this->state(function (array $attributes) {
            return ['status' => 'pending'];
        });
    }
}
