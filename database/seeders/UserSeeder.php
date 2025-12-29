<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // test user 
        User::updateOrCreate(
            ['phone' => '1234567890'], // unique identifier
            [
                'first_name' => 'test',
                'last_name' => 'test',
                'profile_image' => 'profile_images/admin.jpg',
                'id_card_image' => 'id_cards/admin_id.jpg',
                'birth_date' => '1990-01-01',
                'password' => 'testtest',
            ]
        );
        // test user 2
        User::updateOrCreate(
            ['phone' => '0987654321'], // unique identifier
            [
                'first_name' => 'test2',
                'last_name' => 'test2',
                'profile_image' => 'profile_images/admin.jpg',
                'id_card_image' => 'id_cards/admin_id.jpg',
                'birth_date' => '1990-01-01',
                'password' => 'testtest',
            ]
        );

        User::factory()->count(10)->create();
    }
}
