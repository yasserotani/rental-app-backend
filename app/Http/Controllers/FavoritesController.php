<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Apartment;
use Illuminate\Support\Facades\Auth;


class FavoritesController extends Controller
{

    public function toggleFavorite($apartmentId)
    {
        $apartment = Apartment::findOrFail($apartmentId);
        $userId = Auth::id();

        // check if the favorite exist to delete it
        $existing = $apartment->favorites()->where("user_id", $userId)->first();
        if ($existing) {
            //remove favorite 
            $existing->delete();
            return response()->json([
                'status' => 'removed',
                'message' => 'Removed from favorites',
            ], 200);
        }


        // add when it doesn't 
        $favorite = $apartment->favorites()->create([
            "user_id" => $userId,
        ]);

        return response()->json([
            'message' => 'favorite added successfully',
            'data' => $favorite
        ], 201);
    }
    public function getAllUserFavorites()
    {
        $userFavorites = Auth::user()
            ->favorites()
            ->with('apartment')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        if ($userFavorites->isEmpty()) {
            return response()->json([
                'message' => 'no favorites found',
                'favorites' => []
            ], 200);
        }
        return response()->json([
            'message' => 'favorites get it successfully',
            'favorites' => $userFavorites
        ], 200);
    }
}
