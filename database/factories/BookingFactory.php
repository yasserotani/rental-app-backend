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

        // Start date can be up to 12 months in the past OR 12 months in the future
        $startDate = $this->faker->dateTimeBetween('-12 months', '+12 months');

        // End date can be 1 to 365 days after start date (1 year)
        $endDate = (clone $startDate)->modify('+' . rand(1, 365) . ' days');

        $start = Carbon::instance($startDate);
        $end = Carbon::instance($endDate);

        $days = $start->diffInDays($end) + 1;
        $totalPrice = $days * $apartment->price;

        return [
            'user_id' => $user->id,
            'apartment_id' => $apartment->id,
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected', 'cancelled']),
            'total_price' => round($totalPrice, 2),
        ];
    }

    public function configure()
    {
        return $this->afterMaking(function (Booking $booking) {
            if ($booking->apartment_id && !$booking->total_price) {
                $apartment = Apartment::find($booking->apartment_id);
                if ($apartment) {
                    $start = Carbon::parse($booking->start_date);
                    $end = Carbon::parse($booking->end_date);
                    $days = $start->diffInDays($end) + 1;
                    $booking->total_price = round($days * $apartment->price, 2);
                }
            }
        });
    }

    public function approved()
    {
        return $this->state(fn() => ['status' => 'approved']);
    }

    public function pending()
    {
        return $this->state(fn() => ['status' => 'pending']);
    }
}
