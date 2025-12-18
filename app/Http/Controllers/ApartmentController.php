<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
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
}