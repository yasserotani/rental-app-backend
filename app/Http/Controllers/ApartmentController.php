<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use App\Http\Resources\ApartmentResource;

class ApartmentController extends Controller
{

    public function getAllApartments()
    {
        $apartments = Apartment::with('images')->get();

        return response()->json([
            'message' => 'get apartments success',
            'apartments' => ApartmentResource::collection($apartments),
            'count' => $apartments->count()
        ], 200);
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
                'number_of_rooms' => 'required|numeric|min:1',
                'is_rented' => 'sometimes|boolean',
                'images' => 'required|array|min:1',
                'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:4096',
            ]);
            $apartment = Apartment::create([
                'user_id' => auth()->id(), // accept only auth users
                'address' => $request->address,
                'description' => $request->description,
                'city' => $request->city,
                'governorate' => $request->governorate,
                'price' => $request->price,
                'number_of_rooms' => $request->number_of_rooms,
                'is_rented' => $request->is_rented ?? false,//false is default
            ]);
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

    public function getUserApartments(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id'
        ]);

        $apartments = Apartment::where('user_id', $request->user_id)
            ->with('images')
            ->get();

        return response()->json([
            'message' => 'User apartments retrieved successfully',
            'apartments' => ApartmentResource::collection($apartments),
            'count' => $apartments->count()
        ], 200);
    }

}