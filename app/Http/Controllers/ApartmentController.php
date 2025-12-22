<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use Exception;
use Illuminate\Http\Request;

class ApartmentController extends Controller
{
    public function getAllApartments()
    {
        $apartments = Apartment::all();
        return response()->json(
            [
                'message' => 'get appartmenst success',
                'apartments' => $apartments
            ],
            200
        );
    }
    public function createApartments(Request $request)
    {
        try {

            $request->validate([
                'address' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'city' => 'required|string|max:100',
                'governorate' => 'required|string|max:100',
                'price' => 'required|numeric|min:0',
                'is_rented' => 'sometimes|boolean',
                'images' => 'required|array|min:1',
                'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:4096',
            ]);
            $apartment = Apartment::create(
                $request->only([
                    'user_id',
                    'address',
                    'description',
                    'city',
                    'governorate',
                    'price',
                    'is_rented'
                ])
            );
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('public/apartment_images');
                    $apartment->images()->create([
                        'image_path' => $path
                    ]);
                }
            }

            return response()->json(
                ['message' => 'Apartment created successfully'],
                200
            );



        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Apartment creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}