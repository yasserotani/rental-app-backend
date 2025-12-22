<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ApartmentImage;
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

}