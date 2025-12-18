<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
          'is_rented'

     ];
     public function user()
     {
          return $this->belongsTo(User::class);
     }

}