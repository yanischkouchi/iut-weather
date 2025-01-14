<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeatherData extends Model
{
    use HasFactory;

    // Define columns that could be completed with data
    protected $fillable = ['city', 'temperature', 'description', 'forecast'];

    // Cast columns to data types
    protected $casts = [
        'forecast' => 'array',  // Convert JSON of the database in php tab
    ];
}
