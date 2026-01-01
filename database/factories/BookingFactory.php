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

        $today = Carbon::today();
        $start = null;
        $end = null;

        // 25% chance to create a currently active approved booking
        if ($this->faker->boolean(25)) {
            // Start date can be up to 30 days ago
            $startDate = $this->faker->dateTimeBetween('-30 days', 'now');
            $start = Carbon::instance($startDate);

            // End date extends into the future (1 to 90 days from now)
            $endDate = $this->faker->dateTimeBetween('now', '+90 days');
            $end = Carbon::instance($endDate);

            // Ensure the booking overlaps with today
            if ($start->gt($today)) {
                $start = $today->copy();
            }
            if ($end->lt($today)) {
                $end = $today->copy()->addDays(rand(1, 30));
            }
        } else {
            // Regular booking: Start date can be up to 12 months in the past OR 12 months in the future
            $startDate = $this->faker->dateTimeBetween('-12 months', '+12 months');
            // End date can be 1 to 365 days after start date (1 year)
            $endDate = (clone $startDate)->modify('+' . rand(1, 365) . ' days');
            $start = Carbon::instance($startDate);
            $end = Carbon::instance($endDate);
        }

        $days = $start->diffInDays($end) + 1;
        $totalPrice = $days * $apartment->price;

        // If it's a currently active booking, make it approved
        $status = 'pending';
        if ($start && $end && $start->lte($today) && $end->gte($today)) {
            $status = $this->faker->randomElement(['approved', 'pending']); // More likely to be approved if active
        } else {
            $status = $this->faker->randomElement(['pending', 'approved', 'rejected', 'cancelled']);
        }

        return [
            'user_id' => $user->id,
            'apartment_id' => $apartment->id,
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
            'status' => $status,
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

    /**
     * Create an approved booking that is currently active (overlaps with today).
     */
    public function currentlyActive()
    {
        return $this->state(function (array $attributes) {
            $today = Carbon::today();

            // Start date can be up to 30 days ago
            $startDate = $this->faker->dateTimeBetween('-30 days', 'now');
            $start = Carbon::instance($startDate);

            // End date can be 1 to 90 days after start date (ensuring it includes or extends past today)
            $endDate = $this->faker->dateTimeBetween('now', '+90 days');
            $end = Carbon::instance($endDate);

            // Ensure the booking overlaps with today
            if ($start->gt($today)) {
                $start = $today->copy();
            }
            if ($end->lt($today)) {
                $end = $today->copy()->addDays(rand(1, 30));
            }

            // Recalculate total price if apartment is available
            $totalPrice = $attributes['total_price'] ?? 0;
            if (isset($attributes['apartment_id'])) {
                $apartment = Apartment::find($attributes['apartment_id']);
                if ($apartment) {
                    $days = $start->diffInDays($end) + 1;
                    $totalPrice = round($days * $apartment->price, 2);
                }
            }

            return [
                'status' => 'approved',
                'start_date' => $start->format('Y-m-d'),
                'end_date' => $end->format('Y-m-d'),
                'total_price' => $totalPrice,
            ];
        });
    }
}
