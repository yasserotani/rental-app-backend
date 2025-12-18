<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Apartment extends Model
{
     protected $fillable = [
        'user_id',
         'address',
        'description',
        'city',
        'governorate',
        'price',
       'is_rented'
  
     ];
public function user(){
return $this->belongsTo(User::class)   ; 
}

}
