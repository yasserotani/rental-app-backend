<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Apartment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'address',
        'description',
        'city',
        'governorate',
        'price',
        'number_of_rooms',
        'is_rented',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function images()
    {
        return $this->hasMany(ApartmentImage::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
     public function reviews()
    {
        return $this->hasMany(Reviews::class);
    }
}
