<?php

    header('Content-Type: application/json');

    $url = "https://meteo.report/var/data/forecasts/e0c04e2d-8221-48d3-92b9-56e26e743213.json";

    $json = file_get_contents($url);
    $data = json_decode($json, true);

    // Mappa sky_condition (lettere) -> descrizione italiana dettagliata
    $skyConditionMap = [
        "a" => "Cielo sereno",
        "b" => "Soleggiato",
        "c" => "Parzialmente nuvoloso",
        "d" => "Nuvoloso",
        "e" => "Molto nuvoloso",
        "f" => "Rovesci",
        "g" => "Rovesci forti",
        "h" => "Pioggia moderata",
        "i" => "Pioggia forte",
        "j" => "Pioggia debole",
        "k" => "Rovesci deboli",
        "l" => "Neve debole e sole",
        "m" => "Neve e sole",
        "n" => "Neve debole",
        "o" => "Neve moderata",
        "p" => "Neve forte",
        "q" => "Neve bagnata e sole",
        "r" => "Neve bagnata",
        "s" => "Foschia",
        "t" => "Foschia in quota",
        "u" => "Instabile",
        "v" => "Temporali",
        "w" => "Instabile con neve bagnata",
        "x" => "Temporali di neve bagnata",
        "y" => "Instabile con temporali nevosi",
        "z" => "Temporali nevosi",
    ];

    // Mappa di normalizzazione -> categorie generali
    $normalizeMap = [
        "Cielo sereno"                  => "Soleggiato",
        "Soleggiato"                    => "Soleggiato",
        "Parzialmente nuvoloso"         => "Parzialmente Nuvoloso",
        "Nuvoloso"                      => "Parzialmente Nuvoloso",
        "Molto nuvoloso"                => "Nuvoloso",
        "Foschia"                       => "Nuvoloso",
        "Foschia in quota"              => "Nuvoloso",
        "Rovesci deboli"                => "Pioggia",
        "Rovesci"                       => "Pioggia",
        "Rovesci forti"                 => "Pioggia",
        "Pioggia debole"                => "Pioggia",
        "Pioggia moderata"              => "Pioggia",
        "Pioggia forte"                 => "Pioggia",
        "Neve debole e sole"            => "Neve",
        "Neve e sole"                   => "Neve",
        "Neve debole"                   => "Neve",
        "Neve moderata"                 => "Neve",
        "Neve forte"                    => "Neve",
        "Neve bagnata e sole"           => "Neve",
        "Neve bagnata"                  => "Neve",
        "Instabile"                     => "Temporale",
        "Temporali"                     => "Temporale",
        "Instabile con neve bagnata"    => "Temporale",
        "Temporali di neve bagnata"     => "Temporale",
        "Instabile con temporali nevosi"=> "Temporale",
        "Temporali nevosi"              => "Temporale",
    ];

    function getDominantCondition(array $hourlyByOffset, int $dayOffsetMinutes, int $startHour, int $endHour, array $skyConditionMap, array $normalizeMap): ?string {
        $counts = [];
        foreach ($hourlyByOffset as $offsetMinutes => $entry) {
            $minuteOfDay = $offsetMinutes - $dayOffsetMinutes;
            $hourOfDay = $minuteOfDay / 60;
            if ($hourOfDay >= $startHour && $hourOfDay < $endHour) {
                $code = strtolower($entry['sky_condition']);
                $counts[$code] = ($counts[$code] ?? 0) + 1;
            }
        }
        if (empty($counts)) return null;
        arsort($counts);
        $dominantCode = array_key_first($counts);
        $detailed = $skyConditionMap[$dominantCode] ?? $dominantCode;
        return $normalizeMap[$detailed] ?? $detailed;
    }

    $result = [];
    $startTimestamp = strtotime($data['start']);
    $dailyData = $data['1440'];
    $hourlyData = $data['180'];

    $hourlyByOffset = [];
    foreach ($hourlyData as $key => $entry) {
        $offsetMinutes = (int)substr($key, 3);
        $hourlyByOffset[$offsetMinutes] = $entry;
    }

    foreach ($dailyData as $key => $day) {
        $offsetMinutes = (int)substr($key, 4);
        $dayTimestamp = $startTimestamp + ($offsetMinutes * 60);
        $dateStr = date('Y-m-d', $dayTimestamp);

        $morningCondition   = getDominantCondition($hourlyByOffset, $offsetMinutes, 5, 12, $skyConditionMap, $normalizeMap);
        $afternoonCondition = getDominantCondition($hourlyByOffset, $offsetMinutes, 12, 20, $skyConditionMap, $normalizeMap);

        // Fallback sul dato giornaliero
        if (!$morningCondition || !$afternoonCondition) {
            $code = strtolower($day['sky_condition']);
            $detailed = $skyConditionMap[$code] ?? $code;
            $normalized = $normalizeMap[$detailed] ?? $detailed;
            if (!$morningCondition)   $morningCondition   = $normalized;
            if (!$afternoonCondition) $afternoonCondition = $normalized;
        }

        $result[] = [
            'giorno'     => $dateStr,
            'mattina'    => $morningCondition,
            'pomeriggio' => $afternoonCondition,
            'tMin'       => $day['temperature_minimum'],
            'tMax'       => $day['temperature_maximum'],
        ];
    }

    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>