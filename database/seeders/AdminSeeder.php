<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Admin;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         Admin::updateOrCreate(
            ['phone' => '1234567890'], // unique identifier
            [
                'first_name' => 'admin',
                'last_name' => 'admin',
                'password' => 'admin1234'
            ]
        );
    }
}
