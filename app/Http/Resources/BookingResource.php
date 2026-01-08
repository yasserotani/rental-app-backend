<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $status = $this->status;
        if ($status === 'pending') {
            $status = 'pending_approval';
        } elseif ($status === 'approved') {
            $status = $this->end_date < now() ? 'completed' : 'confirmed';
        } elseif ($status === 'rejected') {
            $status = 'rejected';
        } elseif ($status === 'cancelled') {
            $status = 'cancelled';
        }
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'apartment_id' => $this->apartment_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'total_price' => $this->total_price,
            'status' => $status,
            'created_at' => $this->created_at->format('Y-m-d H:i'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i'),
            'apartment' => new ApartmentResource($this->whenLoaded('apartment')),
            'user' => [
                'id' => $this->user_id,
                'first_name' => optional($this->user)->first_name,
                'last_name' => optional($this->user)->last_name,
                'phone' => optional($this->user)->phone,
                'profile_image_url' => $this->user && $this->user->profile_image
                    ? $request->getSchemeAndHttpHost() . '/storage/' . str_replace('public/', '', $this->user->profile_image)
                    : null,
            ],
        ];
    }
}
