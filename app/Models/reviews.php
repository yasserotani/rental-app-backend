<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class reviews extends Model
{
    protected $fillable = ['apartment_id', 'user_id', 'rating', 'comment'];

  public function Apartments(){
    return $this->belongsTo(Reviews::class);
  }
}
