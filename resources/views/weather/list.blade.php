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
        <h1>Météo à {{ $cityName }}</h1>

        @if ($weather)
            <p>Température actuelle : {{ $weather['main']['temp'] }} °C</p>
            <p>Description : {{ $weather['weather'][0]['description'] }}</p>

            @if (!empty($forecast))
                <h2>Prévisions</h2>
                @foreach ($forecast as $date => $forecasts)
                    <details>
                        <summary>
                            {{ $date }} - Température moyenne : {{ round(collect($forecasts)->avg('main.temp'), 2) }} °C
                        </summary>
                        <ul>
                            @foreach ($forecasts as $forecast)
                                <li>
                                    {{ \Carbon\Carbon::createFromTimestamp($forecast['dt'])->format('H:i') }} : 
                                    {{ $forecast['main']['temp'] }} °C - 
                                    {{ $forecast['weather'][0]['description'] }}
                                </li>
                            @endforeach
                        </ul>
                    </details>
                @endforeach
            @endif
        @else
            <p>Impossible de récupérer les données météo.</p>
        @endif
    </div>
    @endsection
</body>
</html>