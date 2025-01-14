<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FavCity extends Model
{
    use HasFactory;

    // fields allowed for mass assignment
    protected $fillable = ['city_name', 'user_id', 'type'];

    // Scope for fav cities
    public function scopeFavorites($query)
    {
        return $query->where('type', 'favorite');
    }

    // Scope for lists cities
    public function scopeListCities($query)
    {
        return $query->where('type', 'list');
    }
}
