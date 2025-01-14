<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WeatherController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::match(['get', 'post'], '/weather', [WeatherController::class, 'showWeatherForm'])->name('weather.form');
    Route::post('/weather/save-city', [WeatherController::class, 'addFavCity'])->name('weather.save_city');
    Route::get('/fav-city/weather', [WeatherController::class, 'showFavCityWeather'])->name('weather.fav_city');
    Route::post('/add-city-to-list', [WeatherController::class, 'addCityToList'])->name('add.city.to.list');
    Route::get('/weather/city/{cityId}', [WeatherController::class, 'showCityWeather'])->name('weather.city');
    Route::delete('/weather/city/{cityId}', [WeatherController::class, 'deleteCity'])->name('weather.city.delete');
    Route::post('/weather/export', [WeatherController::class, 'csvExport'])->name('weather.export');
});

require __DIR__.'/auth.php';
