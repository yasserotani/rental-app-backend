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
        $apartments = Apartment::with('images')->get();

        return response()->json([
            'message' => 'get apartments success',
            'apartments' => ApartmentResource::collection($apartments),
            'count' => $apartments->count(),
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
                'user_id' => Auth::id(), // accept only auth users
                'address' => $request->address,
                'description' => $request->description,
                'city' => $request->city,
                'governorate' => $request->governorate,
                'price' => $request->price,
                'number_of_rooms' => $request->number_of_rooms,
                'is_rented' => $request->is_rented ?? false, // false is default
            ]);
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('public/apartment_images');
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

    public static function getAllApartmentBookings($id)
    {
        $apartmentId = $id;
        $apartment = Apartment::find($apartmentId);
        $bookings = $apartment->bookings()->where('end_date', '>=', now())->get();

        if ($bookings->count() == 0) {
            return response()->json([
                'message' => 'No bookings found !',
            ], 400);
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
if($request->filled('min_rooms')){
$query->where('number_of_rooms','>=',$request->min_rooms);
}
if($request->filled('max_rooms')){
$query->where('number_of_rooms','<=',$request->max_rooms);
}
    $apartments = $query->paginate(10);

    return response()->json($apartments);
}

///

}
