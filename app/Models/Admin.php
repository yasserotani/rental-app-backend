<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable{
        use HasFactory, Notifiable, HasApiTokens;

    protected $fillable=[
        'phone',
        'password',
        'first_name',
        'last_name'
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];
     protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}
