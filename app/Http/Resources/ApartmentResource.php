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
            'address' => $this->address,
            'description' => $this->description,
            'city' => $this->city,
            'governorate' => $this->governorate,
            'price' => $this->price,
            'number_of_rooms' => $this->number_of_rooms,
            'is_rented' => $this->is_rented,

            'images' => $this->images->map(function ($image) {
                // to format the images url
                return [
                    'id' => $image->id,
                    'image_url' => asset(
                        str_replace('public/', 'storage/', $image->image_path)
                    )
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}