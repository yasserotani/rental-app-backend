<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Apartment;
class ApartmentImage extends Model
{
    use HasFactory;
    protected $fillable = ['apartment_id', 'image_path'];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }
}