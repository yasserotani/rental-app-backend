<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApartmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id, // Changed to match DB
            'title' => $this->title,
            'address' => $this->address,
            'description' => $this->description,
            'governorate' => $this->governorate,
            'city' => $this->city,
            'number_of_rooms' => $this->number_of_rooms, // Changed to match DB
            'area' => $this->area,
            'price' => $this->price,
            'is_rented' => $this->is_rented, // Changed to match DB
            'average_rating' => $this->average_rating,
            'reviews_count' => $this->reviews_count,
            'created_at' => $this->created_at->format('Y-m-d H:i'),

            'images' => $this->images->map(function ($image) use ($request) { // build absolute URL based on request host
                return [
                    'id' => $image->id,
                    'image_url' => $request->getSchemeAndHttpHost() . '/storage/' . str_replace('public/', '', $image->image_path)
                ];
            }),


            'updated_at' => $this->updated_at->format('Y-m-d'),
        ];
    }
}
