<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
  protected $table = 'reviews';

  protected $fillable = ['apartment_id', 'user_id', 'rating', 'comment'];

  public function apartment()
  {
    return $this->belongsTo(Apartment::class);
  }

  public function user()
  {
    return $this->belongsTo(User::class);
  }
}
