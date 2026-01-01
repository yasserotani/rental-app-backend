<?php

namespace App\Http\Controllers;

use App\Http\Resources\ApartmentResource;
use App\Models\Apartment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApartmentController extends Controller
{
    public function getAllApartments()
    {
        $apartments = Apartment::with('images')->paginate(10);

        return response()->json([
            'message' => 'get apartments success',
            'data' => ApartmentResource::collection($apartments),
            'pagination' => [
                'current_page' => $apartments->currentPage(),
                'last_page' => $apartments->lastPage(),
                'per_page' => $apartments->perPage(),
                'total' => $apartments->total(),
                'next_page_url' => $apartments->nextPageUrl(),
                'prev_page_url' => $apartments->previousPageUrl(),
            ],
        ], 200);
    }

    public function createApartments(Request $request)
    {
        try {

            $request->validate([
                'title' => 'required|string|max:255',
                'address' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'governorate' => 'required|string|max:100',
                'city' => 'required|string|max:100',
                'number_of_rooms' => 'required|numeric|min:1',
                'area' => 'required|numeric|min:0',
                'price' => 'required|numeric|min:0',
                'is_rented' => 'sometimes|boolean',
                'images' => 'required|array|min:1',
                'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:4096',
            ]);


            $apartment = Apartment::create([
                'user_id' => Auth::id(), // accept only auth users
                'title' => $request->title,
                'address' => $request->address,
                'description' => $request->description,
                'governorate' => $request->governorate,
                'city' => $request->city,
                'number_of_rooms' => $request->number_of_rooms,
                'area' => $request->area,
                'price' => $request->price,
                'is_rented' => $request->is_rented ?? false, // false is default
            ]);
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('apartment_images', 'public');
                    $apartment->images()->create([
                        'image_path' => $path,
                    ]);
                }
            }
            return response()->json(
                [
                    'message' => 'Apartment created successfully',
                    'data' => $apartment
                ],
                200
            );
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Apartment creation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getUserApartments(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $apartments = Apartment::where('user_id', $request->user_id)
            ->with('images')
            ->get();

        return response()->json([
            'message' => 'User apartments retrieved successfully',
            'apartments' => ApartmentResource::collection($apartments),
            'count' => $apartments->count(),
        ], 200);
    }

    public function getAllApartmentBookings($id)
    {
        $apartment = Apartment::findOrFail($id);

        // Check if user owns the apartment
        if ($apartment->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'You do not own this apartment!',
            ], 403);
        }

        $bookings = $apartment->bookings()->where('end_date', '>=', now())->get();

        if ($bookings->isEmpty()) {
            return response()->json([
                'message' => 'No bookings found!',
            ], 404);
        }

        return response()->json([
            'message' => 'Bookings retrieved successfully',
            'bookings' => $bookings,
        ], 200);
    }
    public function search(Request $request)
    {
        $query = Apartment::query();

        if ($request->filled('governorate')) {
            $query->where('governorate', $request->governorate);
        }

        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }
        if ($request->filled('min_rooms')) {
            $query->where('number_of_rooms', '>=', $request->min_rooms);
        }
        if ($request->filled('max_rooms')) {
            $query->where('number_of_rooms', '<=', $request->max_rooms);
        }
        $apartments = $query->paginate(10);

        return response()->json($apartments);
    }

    ///

}
