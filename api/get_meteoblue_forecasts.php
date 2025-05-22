<?php

    // Ritorna il JSON
    header('Content-Type: application/json');

    $env = parse_ini_file('../.env'); // Adatta il percorso se necessario
    $apiKey = $env['API_KEY_METEOBLUE'];

    // URL dei dati meteo
    $url = "https://my.meteoblue.com/packages/basic-1h?apikey=$apiKey&lat=46.0679&lon=11.1211&asl=122&format=json&forecast_days=7";

    // Recupera il contenuto JSON
    $json = file_get_contents($url);
    $data = json_decode($json, true);

    // Estrai array principali
    $times = $data['data_1h']['time'];
    $codes = $data['data_1h']['pictocode'];
    $temperatures = $data['data_1h']['temperature'];

    // Dati raccolti per giorno
    $morning_codes = [];
    $afternoon_codes = [];
    $day_temperatures = [];

    // Raggruppa per giorno
    foreach ($times as $index => $datetime) {
        [$date, $hour] = explode(' ', $datetime);
        $hourInt = (int)substr($hour, 0, 2);
        $temp = $temperatures[$index];

        // Temperature per giorno
        $day_temperatures[$date][] = $temp;

        // Codici meteo per fascia oraria
        if ($hourInt >= 5 && $hourInt <= 11) {
            $morning_codes[$date][] = $codes[$index];
        } elseif ($hourInt >= 12 && $hourInt <= 20) {
            $afternoon_codes[$date][] = $codes[$index];
        }
    }

    // Funzione per trovare il valore più frequente
    function getMostCommonCode($array) {
        if (empty($array)) return null;
        $counts = array_count_values($array);
        arsort($counts);
        return array_key_first($counts);
    }

    // Mappa di associazione per i pictocode
    $pictocodeDescriptions = [
        // Soleggiato
        1 => "Soleggiato",
        2 => "Soleggiato",
        3 => "Soleggiato",
        13 => "Soleggiato",
        14 => "Soleggiato",
        15 => "Soleggiato",

        // Parzialmente Nuvoloso
        4 => "Parzialmente Nuvoloso",
        5 => "Parzialmente Nuvoloso",
        6 => "Parzialmente Nuvoloso",

        // Nuvoloso
        7 => "Nuvoloso",
        8 => "Nuvoloso",
        9 => "Nuvoloso",
        10 => "Nuvoloso",
        11 => "Nuvoloso",
        12 => "Nuvoloso",
        16 => "Nuvoloso",
        17 => "Nuvoloso",
        18 => "Nuvoloso",
        19 => "Nuvoloso",
        20 => "Nuvoloso",
        21 => "Nuvoloso",
        22 => "Nuvoloso",

        // Pioggia
        23 => "Pioggia",
        25 => "Pioggia",
        31 => "Pioggia",
        33 => "Pioggia",
        35 => "Pioggia",

        // Neve
        24 => "Neve",
        26 => "Neve",
        32 => "Neve",
        34 => "Neve",

        // Temporale
        27 => "Temporale",
        28 => "Temporale",
        29 => "Temporale",
        30 => "Temporale",
    ];

    // Costruzione array finale
    $results = [];

    foreach ($day_temperatures as $day => $temps) {
        $most_common_morning = getMostCommonCode($morning_codes[$day] ?? []);
        $most_common_afternoon = getMostCommonCode($afternoon_codes[$day] ?? []);
        $tMin = min($temps);
        $tMax = max($temps);

        $results[] = [
            'giorno' => $day,
            'mattina' => $pictocodeDescriptions[$most_common_morning],
            'pomeriggio' => $pictocodeDescriptions[$most_common_afternoon],
            'tMin' => $tMin,
            'tMax' => $tMax
        ];
    }

    // Output finale
    echo json_encode($results, JSON_PRETTY_PRINT);
?>