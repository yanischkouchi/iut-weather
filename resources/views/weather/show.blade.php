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
            @if ($weather)
                <h2>Météo à {{ $weather['name'] }}</h2>
                <p>Température : {{ $weather['main']['temp'] }} °C</p>
                <p>Description : {{ $weather['weather'][0]['description'] }}</p>

                @if (!empty($forecast))
                    <h3>Prévisions</h3>
                    @foreach ($forecast as $date => $forecasts)
                        <details>
                            <summary class="forecast">
                                {{ $date }} - Température moyenne : {{ round(collect($forecasts)->avg('main.temp'), 2) }} °C
                            </summary>
                            <ul>
                                @foreach ($forecasts as $forecast)
                                    <li>
                                        🕐 {{ \Carbon\Carbon::createFromTimestamp($forecast['dt'])->format('H:i') }} - 
                                        Température : {{ $forecast['main']['temp'] }} °C - 
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