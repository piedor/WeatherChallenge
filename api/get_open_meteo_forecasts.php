<?php

    // Ritorna il JSON
    header('Content-Type: application/json');

    require_once '../utils/settings.php';

    // URL dei dati meteo
    $url = "https://api.open-meteo.com/v1/forecast?latitude=46.0679&longitude=11.1211&daily=temperature_2m_max,temperature_2m_min&hourly=weather_code";

    // Recupera il contenuto JSON
    $json = file_get_contents($url);
    $data = json_decode($json, true);

    $times = $data['hourly']['time'];
    $codes = $data['hourly']['weather_code'];
    $tempsMax = $data['daily']['temperature_2m_max'];
    $tempsMin = $data['daily']['temperature_2m_min'];

    $results = [];
    $index = 0;

    foreach ($data['daily']['time'] as $day) {
        $morning_codes = [];
        $afternoon_codes = [];

        for ($i = 0; $i < count($times); $i++) {
            $datetime = $times[$i];
            $hour = (int)substr($datetime, 11, 2);
            $date = substr($datetime, 0, 10);

            if ($date === $day) {
                if ($hour >= 5 && $hour <= 11) {
                    $morning_codes[] = $codes[$i];
                } elseif ($hour >= 12 && $hour <= 20) {
                    $afternoon_codes[] = $codes[$i];
                }
            }
        }

        $most_common_morning = mostFrequentCode($morning_codes);
        $most_common_afternoon = mostFrequentCode($afternoon_codes);

        $results[$day] = [
            'morning' => $most_common_morning,
            'afternoon' => $most_common_afternoon,
            'tMin'=> $tempsMin[$index],
            'tMax'=> $tempsMax[$index]
        ];

        $index++;
    }

    // Funzione per trovare il valore più frequente
    function mostFrequentCode($arr) {
        if (empty($arr)) return null;
        $counts = array_count_values($arr);
        arsort($counts);
        return array_key_first($counts);
    }

    // Funzione per ottenere descrizione testuale da codice
    function getWeatherDescription($code, $wmoCodeToDescEmoji, $weatherDescToEmoji) {
        if (!isset($wmoCodeToDescEmoji[$code])) {
            return "Descrizione sconosciuta";
        }

        list($desc, $emoji) = $wmoCodeToDescEmoji[$code];

        $emojiToDesc = array_flip($weatherDescToEmoji);

        return $emojiToDesc[$emoji];
    }

    // Risultato finale
    $result = [];

    foreach ($results as $date => $periods) {
        $giornoData = [
            'giorno' => $date,
            'mattina' => getWeatherDescription($periods['morning'], $wmoCodeToDescEmoji, $weatherDescToEmoji),
            'pomeriggio' => getWeatherDescription($periods['afternoon'], $wmoCodeToDescEmoji, $weatherDescToEmoji),
            'tMin'=> $periods['tMin'],
            'tMax'=> $periods['tMax']
        ];
        $result[] = $giornoData;
    }

    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>