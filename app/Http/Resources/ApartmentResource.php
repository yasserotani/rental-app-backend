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
            'userId' => $this->user_id,
            'title' => $this->title,
            'address' => $this->address,
            'description' => $this->description,
            'governorate' => $this->governorate,
            'city' => $this->city,
            'rooms' => $this->number_of_rooms,
            'area' => $this->area,
            'price' => $this->price,
            'isRented' => $this->is_rented,
            'average_rating' => $this->average_rating,
            'reviews_count' => $this->reviews_count,
            'created_at' => $this->created_at->format('Y-m-d H:i'),

            'imageUrls' => $this->images->map(function ($image) {
                // to format the images url
                return [
                    'id' => $image->id,
                    'image_url' => asset('storage/' . str_replace('public/', '', $image->image_path))
                ];
            }),


            'updated_at' => $this->updated_at->format('Y-m-d'),
        ];
    }
}
