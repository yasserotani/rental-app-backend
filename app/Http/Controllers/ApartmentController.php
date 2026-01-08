<?php

namespace App\Http\Controllers;

use App\Http\Resources\ApartmentResource;
use App\Models\Apartment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ApartmentController extends Controller
{
    public function getAllApartments()
    {
        $apartments = Apartment::with('images')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'get apartments success',
            'data' => ApartmentResource::collection($apartments),
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
                    'data' => new ApartmentResource($apartment)
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

    // update apartment data
    public function updateApartment(Request $request, $id)
    {
        $request->validate([
            'title' => 'sometimes|string|max:255',
            'address' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'governorate' => 'sometimes|string|max:100',
            'city' => 'sometimes|string|max:100',
            'number_of_rooms' => 'sometimes|numeric|min:1',
            'area' => 'sometimes|numeric|min:0',
            'price' => 'sometimes|numeric|min:0',
            'is_rented' => 'sometimes|boolean',
        ]);
        // check if the user own the apartment
        $apartment = Apartment::findOrFail($id);
        if ($apartment->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'You do not own this apartment!'
            ], 400);
        }
        $apartment->update($request->only([
            'title',
            'address',
            'description',
            'governorate',
            'city',
            'number_of_rooms',
            'area',
            'price',
            'is_rented',
        ]));
        return response()->json([
            'message' => 'Apartment updated successfully',
            'apartment' => new ApartmentResource($apartment)
        ], 200);
    }
    // add image to apartment
    public function addImages(Request $request, $id)
    {
        try {
            $request->validate([
                'images' => 'required|array|min:1',
                'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:4096',
            ]);
            $apartment = Apartment::findOrFail($id);
            // check if the user own the apartment
            if ($apartment->user_id !== Auth::id()) {
                return response()->json([
                    'message' => 'You do not own this apartment!'
                ], 400);
            }
            $addedImages = [];
            $failed = [];
            $received = $request->hasFile('images') ? count($request->file('images')) : 0;
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    try {
                        $path = $image->store('apartment_images', 'public');
                        $newImage = $apartment->images()->create([
                            'image_path' => $path,
                        ]);
                        $addedImages[] = [
                            'id' => $newImage->id,
                            'image_url' => $request->getSchemeAndHttpHost() . '/storage/' . str_replace('public/', '', $newImage->image_path)
                        ];
                    } catch (\Throwable $e) {
                        $failed[] = [
                            'name' => $image->getClientOriginalName(),
                            'error' => $e->getMessage(),
                        ];
                    }
                }
            }
            return response()->json([
                'message' => 'Images added successfully',
                'images' => $addedImages,
                'received_count' => $received,
                'added_count' => count($addedImages),
                'failed' => $failed,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Image add failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    // delete images
    public function deleteImages(Request $request, $id)
    {
        $request->validate([
            'image_ids' => 'required|array|min:1',
            'image_ids.*' => 'integer|exists:apartment_images,id',
        ]);

        $apartment = Apartment::findOrFail($id);

        if ($apartment->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'You do not own this apartment!'
            ], 403);
        }

        $images = $apartment->images()
            ->whereIn('id', $request->image_ids)
            ->get();

        if ($images->isEmpty()) {
            return response()->json([
                'message' => 'No images found'
            ], 404);
        }

        foreach ($images as $image) {
            Storage::disk('public')->delete($image->image_path);
            $image->delete();
        }

        return response()->json([
            'message' => 'Images deleted successfully',
            'deleted_count' => $images->count()
        ], 200);
    }

    // get all user apartments
    public function getUserApartments()
    {
        $userID = Auth::id();
        $apartments = Apartment::where('user_id', $userID)
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

        $bookings = $apartment->bookings()->get();

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
        $query = Apartment::with('images');

        if ($request->filled('title')) {
            $query->where('title', $request->governorate);
        }
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

        return response()->json([
            'message' => 'Search results',
            'data' => ApartmentResource::collection($apartments),
            'pagination' => [
                'current_page' => $apartments->currentPage(),
                'last_page' => $apartments->lastPage(),
            ]
        ]);
    }
    // delete apartment

    public function delete($id)
    {
        $apartment = Apartment::with('images')->with('bookings')->findOrFail($id);

        if ($apartment->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'You do not own this apartment!'
            ], 403);
        }

        // Store ID before deletion
        $apartmentId = $apartment->id;

        foreach ($apartment->images as $image) {
            Storage::disk('public')->delete($image->image_path);
        }
        $apartment->delete();

        return response()->json([
            'message' => 'Apartment deleted successfully',
            'apartment_id' => $apartmentId
        ], 200);
    }

    ///

}
