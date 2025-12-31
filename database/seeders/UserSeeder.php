<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\Apartment;
use App\Models\Booking;
use App\Models\Review;
use App\Models\Favorite;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // test user 
        $testUser1 = User::updateOrCreate(
            ['phone' => '1234567890'], // unique identifier
            [
                'first_name' => 'test',
                'last_name' => 'test',
                'profile_image' => 'profile_images/admin.jpg',
                'id_card_image' => 'id_cards/admin_id.jpg',
                'birth_date' => '1990-01-01',
                'status' => 'approved',
                'password' => 'testtest',
            ]
        );

        // test user 2
        $testUser2 = User::updateOrCreate(
            ['phone' => '0987654321'], // unique identifier
            [
                'first_name' => 'test2',
                'last_name' => 'test2',
                'profile_image' => 'profile_images/admin.jpg',
                'id_card_image' => 'id_cards/admin_id.jpg',
                'birth_date' => '1990-01-01',
                'status' => 'approved',
                'password' => 'testtest',
            ]
        );

        // Create 10 additional users with factories
        $users = User::factory()->count(10)->create();

        // Add test users to the collection
        $allUsers = collect([$testUser1, $testUser2])->merge($users);

        // For each user, create apartments, bookings, reviews, and favorites
        foreach ($allUsers as $user) {
            // Create 1-3 apartments for each user
            Apartment::factory()
                ->count(rand(1, 3))
                ->state(['user_id' => $user->id])
                ->create();
        }

        // Get all apartments after they're all created
        $allApartments = Apartment::all();

        // Create bookings, reviews, and favorites for each user
        foreach ($allUsers as $user) {
            // Get apartments that don't belong to this user
            $otherApartments = $allApartments->where('user_id', '!=', $user->id);

            if ($otherApartments->count() > 0) {
                // Create 2-5 bookings for each user (booking other users' apartments)
                $bookingCount = rand(2, min(5, $otherApartments->count()));
                $selectedApartments = $otherApartments->random($bookingCount);

                foreach ($selectedApartments as $apartment) {
                    $startDate = Carbon::now()->addDays(rand(1, 30));
                    $endDate = (clone $startDate)->addDays(rand(1, 7));
                    $days = $startDate->diffInDays($endDate) + 1;
                    $totalPrice = round($days * $apartment->price, 2);

                    Booking::factory()
                        ->state([
                            'user_id' => $user->id,
                            'apartment_id' => $apartment->id,
                            'start_date' => $startDate->format('Y-m-d'),
                            'end_date' => $endDate->format('Y-m-d'),
                            'total_price' => $totalPrice,
                        ])
                        ->create();
                }

                // Create 1-3 reviews for each user (reviewing apartments)
                $reviewCount = rand(1, min(3, $otherApartments->count()));
                $reviewedApartments = $otherApartments->random($reviewCount);

                foreach ($reviewedApartments as $apartment) {
                    Review::factory()
                        ->state([
                            'user_id' => $user->id,
                            'apartment_id' => $apartment->id,
                        ])
                        ->create();
                }

                // Create 2-4 favorites for each user (favoriting apartments)
                $favoriteCount = rand(2, min(4, $otherApartments->count()));
                $favoritedApartments = $otherApartments->random($favoriteCount);

                foreach ($favoritedApartments as $apartment) {
                    Favorite::factory()
                        ->state([
                            'user_id' => $user->id,
                            'apartment_id' => $apartment->id,
                        ])
                        ->create();
                }
            }
        }
    }
}
