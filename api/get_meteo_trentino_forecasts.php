<?php

    // Ritorna il JSON
    header('Content-Type: application/json');

    // URL dei dati meteo
    $url = "https://www.meteotrentino.it/protcivtn-meteo/api/front/previsioneOpenDataLocalita?localita=TRENTO";

    // Recupera il contenuto JSON
    $json = file_get_contents($url);
    $data = json_decode($json, true);

    // Mappa di associazione per le descrizioni
    $iconDescMap = [
        "Sereno" => "Soleggiato",
        "Poco nuvoloso" => "Soleggiato",
        "Poco nuvoloso con piogge deboli" => "Parzialmente Nuvoloso",
        "Poco nuvoloso con nevicate deboli" => "Parzialmente Nuvoloso",
        "Poco nuvoloso con deboli piogge/nevicate" => "Parzialmente Nuvoloso",

        "Nuvoloso" => "Parzialmente Nuvoloso",
        "Nuvoloso con piogge deboli" => "Pioggia",
        "Nuvoloso con piogge moderate" => "Pioggia",
        "Nuvoloso con deboli piogge/nevicate" => "Pioggia",
        "Nuvoloso con moderate piogge/nevicate" => "Pioggia",
        "Nuvoloso con nevicate deboli" => "Neve",
        "Nuvoloso con nevicate moderate" => "Neve",

        "Molto nuvoloso" => "Nuvoloso",
        "Molto nuvoloso con piogge deboli" => "Pioggia",
        "Molto nuvoloso con piogge moderate" => "Pioggia",
        "Molto nuvoloso con forti piogge" => "Pioggia",
        "Molto nuvoloso con deboli piogge/nevicate" => "Pioggia",
        "Molto nuvoloso con moderate piogge/nevicate" => "Pioggia",
        "Molto nuvoloso con nevicate deboli" => "Neve",
        "Molto nuvoloso con nevicate moderate" => "Neve",

        "Coperto" => "Nuvoloso",
        "Coperto con piogge deboli" => "Pioggia",
        "Coperto con piogge moderate" => "Pioggia",
        "Coperto con piogge forti" => "Pioggia",
        "Coperto con deboli piogge/nevicate" => "Pioggia",
        "Coperto con moderate piogge/nevicate" => "Pioggia",
        "Coperto con forti piogge/nevicate" => "Pioggia",
        "Coperto con nevicate deboli" => "Neve",
        "Coperto con nevicate moderate" => "Neve",
        "Coperto con nevicate forti" => "Neve",

        "Rovesci o temporali" => "Temporale",
        "Nebbia" => "Nuvoloso",
    ];

    // Risultato finale
    $result = [];

    foreach ($data['previsione'][0]['giorni'] as $giorno) {
        $giornoData = [
            'giorno' => $giorno['giorno'],
            'mattina' => null,
            'pomeriggio' => null,
            'tMin'=> $giorno['tMinGiorno'],
            'tMax'=> $giorno['tMaxGiorno']
        ];

        foreach ($giorno['fasce'] as $fascia) {
            if ($fascia['fasciaPer'] === 'mattina') {
                $desc = $fascia['descIcona'];
                $giornoData['mattina'] = $iconDescMap[$desc] ?? $desc;
            } elseif ($fascia['fasciaPer'] === 'pomeriggio') {
                $desc = $fascia['descIcona'];
                $giornoData['pomeriggio'] = $iconDescMap[$desc] ?? $desc;
            }
        }



        $result[] = $giornoData;
    }

    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>