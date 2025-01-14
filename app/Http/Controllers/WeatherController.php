<?php
namespace App\Http\Controllers;

use App\Models\WeatherData;
use App\Models\UserCity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Http;

class WeatherController extends Controller
{
    // to manage display of the form
    public function showWeatherForm(Request $request)
    {
        $weather = null;
        $dailyForecasts = [];

        if ($request->isMethod('post')) {
            $city = $request->input('city');
            if ($city) {
                $coords = $this->getCoordinates($city);

                if ($coords) {
                    // request weather and forecast data
                    list($weatherData, $forecastData) = $this->getWeatherData($coords['lat'], $coords['lon']);

                    if ($weatherData && $forecastData) {
                        // to update or create weather data in DB
                        $weather = WeatherData::updateOrCreate(
                            ['city' => $city],
                            [
                                'city' => $city,
                                'temperature' => $weatherData['main']['temp'] ?? 'Données non disponibles',
                                'description' => $weatherData['weather'][0]['description'] ?? 'Données non disponibles',
                                'forecast' => json_encode($forecastData['list'] ?? []),
                            ]
                        );

                        // group forecast per day
                        $dailyForecasts = $this->groupForecastByDay($forecastData['list']);
                    }
                } else {
                    return redirect()->back()->withErrors([
                        'message' => 'Impossible de récupérer les coordonnées pour la ville.'
                    ]);
                }
            } else {
                return redirect()->back()->withErrors([
                    'message' => 'Le paramètre de ville est requis.'
                ]);
            }
        }

        return view('weather.form', ['weather' => $weather, 'dailyForecasts' => $dailyForecasts]);
    }

    private function getWeatherData($lat, $lon)
    {
        // request current weather
        $weatherResponse = Http::get(config('services.openweather.url') . 'data/2.5/weather', [
            'lat' => $lat,
            'lon' => $lon,
            'appid' => config('services.openweather.key'),
            'units' => 'metric',
        ]);

        if (!$weatherResponse->successful()) {
            \Log::error('Erreur dans la réponse météo actuelle', [
                'response' => $weatherResponse->body(),
            ]);
            return [null, null];
        }

        // request forecast data
        $forecastResponse = Http::get(config('services.openweather.url') . 'data/2.5/forecast', [
            'lat' => $lat,
            'lon' => $lon,
            'appid' => config('services.openweather.key'),
            'units' => 'metric',
        ]);

        if (!$forecastResponse->successful()) {
            \Log::error('Erreur dans la réponse des prévisions météo', [
                'response' => $forecastResponse->body(),
            ]);
            return [null, null];
        }

        return [$weatherResponse->json(), $forecastResponse->json()];
    }

    private function groupForecastByDay($forecastList)
    {
        $dailyForecasts = [];
        foreach ($forecastList as $day) {
            $hour = \Carbon\Carbon::createFromTimestamp($day['dt'])->format('H');
            // get forecast each 6 hours (initially it's each 3hours)
            if (in_array($hour, ['03', '09', '15', '21'])) {
                $date = \Carbon\Carbon::createFromTimestamp($day['dt'])->format('d-m-Y');
                $dailyForecasts[$date][] = $day;
            }
        }
        return $dailyForecasts;
    }

    private function getCoordinates($city)
    {
        // Sending request to get coordinates
        $response = Http::get(config('services.openweather.url') . 'geo/1.0/direct', [
            'q' => $city,
            'appid' => config('services.openweather.key'),
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return $data[0] ?? null;
        } else {
            $error = $response->json();
            throw new \Exception("Impossible de récupérer les coordonnées pour la ville. Détails de l'erreur : " . json_encode($error));
        }
    }

    public function addFavCity(Request $request)
    {
        $request->validate([
            'city_name' => 'required|string|max:255',
        ]);

        auth()->user()->favCity()->updateOrCreate([], [
            'city_name' => $request->input('city_name'),
            'type' => 'favorite',
        ]);

        return redirect()->back()->with('status', 'Ville enregistrée avec succès!');
    }

    public function addCityToList(Request $request)
    {
        $request->validate([
            'city_name' => 'required|string|max:255',
        ]);

        // Add city with type "list"
        auth()->user()->listCities()->firstOrCreate([
            'user_id' => auth()->id(),
            'city_name' => $request->input('city_name'),
            'type' => 'list',
        ]);

        return redirect()->back()->with('status', 'Ville ajoutée avec succès à votre liste !');
    }

    public function showCityWeather($cityId)
    {
        // get city from id
        $city = auth()->user()->listCities()->find($cityId);

        if (!$city) {
            return redirect()->route('weather.form')->with('status', 'Cette ville n\'existe pas.');
        }

        $coords = $this->getCoordinates($city->city_name);

        if ($coords) {
            // get weather and forecast data of this city
            list($weatherData, $forecastData) = $this->getWeatherData($coords['lat'], $coords['lon']);

            if ($weatherData && $forecastData) {
                // group forecast per day
                $dailyForecasts = $this->groupForecastByDay($forecastData['list']);

                return view('weather.show', [
                    'weather' => $weatherData,
                    'forecast' => $dailyForecasts,
                ]);
            }
        }

        return redirect()->back()->withErrors(['message' => 'Impossible de récupérer les coordonnées de la ville.']);
    }   

    public function showFavCityWeather()
    {
        // check if user has favorite city
        $favCity = auth()->user()->favCity;

        if (!$favCity) {
            return redirect()->route('weather.form')->with('status', 'Aucune ville favorite définie.');
        }

        $coords = $this->getCoordinates($favCity->city_name);

        if ($coords) {
            // get weather and forecast data for the favorite city
            list($weatherData, $forecastData) = $this->getWeatherData($coords['lat'], $coords['lon']);

            if ($weatherData && $forecastData) {
                // group forecast per day
                $dailyForecasts = $this->groupForecastByDay($forecastData['list']);

                return view('weather.show', [
                    'weather' => $weatherData,
                    'forecast' => $dailyForecasts,
                ]);
            }
        }

        return redirect()->back()->withErrors(['message' => 'Impossible de récupérer les coordonnées de la ville favorite.']);
    }

    public function deleteCity($cityId)
    {
        // get city from id
        $city = auth()->user()->listCities()->find($cityId);

        if (!$city) {
            return redirect()->back()->withErrors(['message' => 'Ville introuvable ou non autorisée.']);
        }

        $city->delete();

        return redirect()->route('dashboard')->with('status', 'La ville a été supprimée de votre liste.');
    }

    public function csvExport(Request $request)
    {
        $forecasts = $request->input('forecasts');
        // $cityName = $request->input('city_name');

        if (!$forecasts) {
            return redirect()->back()->withErrors(['error' => 'Aucune donnée à exporter.']);
        }

        // convert forecasts in array
        $data = json_decode($forecasts, true);

        $csvData = "Date,Heure,Température,Description\n";
        foreach ($data as $date => $forecastsPerDay) {
            foreach ($forecastsPerDay as $forecast) {
                $time = \Carbon\Carbon::createFromTimestamp($forecast['dt'])->format('H:i');
                $temperature = $forecast['main']['temp'];
                $description = $forecast['weather'][0]['description'];

                $csvData .= "$date,$time,$temperature,$description\n";
            }
        }

        // $fileName = "previsions_{$cityName}.csv";

        // response http with csv file
        return Response::make($csvData, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"previsions\"", // to download the file
        ]);
    }

}