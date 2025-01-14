<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Météo</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    @extends('layouts.app')

    @section('content')
    <div class="container">
        <h1>Consulter la Météo</h1>

        <!-- Form of city selection -->
        <form method="POST" action="{{ url('/weather') }}">
            @csrf
            <label for="city">Entrez le nom de la ville :</label>
            <input type="text" id="city" name="city" value="{{ old('city') }}" required>
            <button type="submit" class="btn" id="rechercher">Rechercher</button>
        </form>

        <!-- if data available => return weather data -->
        @if($weather)
            <h2>Météo à {{ $weather->city }}</h2>
            <p>Température : {{ $weather->temperature }} °C</p>
            <p>Description : {{ $weather->description }}</p>
            <form action="{{ route('weather.save_city') }}" method="POST" style="display: inline;">
                @csrf
                <input type="hidden" name="city_name" value="{{ $weather->city }}">
                <button type="submit" class="btn" id="fav">Rendre Favori ⭐</button>
            </form>
            <form action="{{ route('add.city.to.list') }}" method="POST" style="display: inline;">
                @csrf
                <input type="hidden" name="city_name" value="{{ $weather->city }}">
                <button type="submit" class="btn" id="list">Ajouter à la liste</button>
            </form>
            <!-- Return only first weather data for each day  -->
            @if(!empty($weather->forecast))
                <h3>Prévisions</h3>
                <form action="{{ route('weather.export') }}" method="POST" style="display: inline;">
                    @csrf
                    <input type="hidden" name="forecasts" value="{{ json_encode($dailyForecasts) }}">
                    <button type="submit" class="btn" id="export-csv">Exporter les prévisions (CSV)</button>
                </form>
                @if(!empty($dailyForecasts))
                    @foreach ($dailyForecasts as $date => $forecasts)
                        <details>
                            <summary class="forecast">
                                {{ $date }} - Température moyenne : {{ round(collect($forecasts)->avg('main.temp'), 2) }} °C
                            </summary>
                            <ul>
                                @foreach ($forecasts as $forecast)
                                    <li>
                                    🕐 {{ \Carbon\Carbon::createFromTimestamp($forecast['dt'])->format('H:i') }} - 
                                        {{ $forecast['main']['temp'] }} °C - {{ $forecast['weather'][0]['description'] }}
                                    </li>
                                @endforeach
                            </ul>
                        </details>
                    @endforeach
                @endif
            @endif
            <!-- Return all weather data -->
            <!-- @if(!empty($weather->forecast))
                <h3>Prévisions</h3>
                <ul>
                    @foreach (json_decode($weather->forecast, true) as $day)
                        <li>{{ \Carbon\Carbon::createFromTimestamp($day['dt'])->format('d-m-Y') }} - {{ $day['main']['temp'] }} °C - {{ $day['weather'][0]['description'] }}</li>
                    @endforeach
                </ul>
            @endif -->
        @endif

        <!-- Return error -->
        @if($errors->any())
            <div class="errors">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
    </div>
    @endsection
</body>
</html>
