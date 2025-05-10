<?php
    header("Content-Type: application/json");

    require_once '../utils/settings.php';

    // Verifica se la richiesta è POST e contiene i dati necessari
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(["error" => "Metodo non consentito. Usa POST."]);
        exit;
    }

    $input = json_decode(file_get_contents("php://input"), true);

    // Controlla se i parametri richiesti sono presenti
    if (!isset($input['weather_codes']) || !isset($input['morning_desc']) || !isset($input['afternoon_desc'])) {
        echo json_encode(["error" => "Dati mancanti. Invia weather_codes, morning_desc, e afternoon_desc"]);
        exit;
    }

    // Parametri ricevuti dall'API
    $weatherCodes = $input['weather_codes']; // Array di codici meteo (24 valori)
    $morningDesc = $input['morning_desc'];   // Previsione studente mattina
    $afternoonDesc = $input['afternoon_desc']; // Previsione studente pomeriggio

    // Funzione per determinare la condizione prevalente
    function getDominantConditions($weatherCodes, $mapping, $wmoCodeToDescEmoji, $threshold = 0.2) {
        $totalCodes = count($weatherCodes);
        $frequencies = array_count_values($weatherCodes);

        $descGroups = [];

        // Filtra codici sopra la soglia e raggruppa per descrizione
        foreach ($frequencies as $code => $frequency) {
            $presence = $frequency / $totalCodes;
            if ($presence >= $threshold && isset($wmoCodeToDescEmoji[$code])) {
                $desc = $wmoCodeToDescEmoji[$code][0];
                $descGroups[$desc]['codes'][$code] = $frequency;
                $descGroups[$desc]['total'] = ($descGroups[$desc]['total'] ?? 0) + $frequency;
            }
        }

        // Ordina per frequenza totale decrescente
        uasort($descGroups, function($a, $b) {
            return $b['total'] <=> $a['total'];
        });

        // Crea lista finale dei codici dominanti, ordinati
        $dominantCodes = [];
        foreach ($descGroups as $group) {
            arsort($group['codes']); // opzionale: ordina codici nello stesso gruppo per frequenza
            foreach ($group['codes'] as $code => $_) {
                $dominantCodes[] = $code;
            }
        }
    
        // Cerca i simboli associati ai codici dominanti
        $emojiUsage = []; // emoji => [groupIndex, position]
        $groupedConditions = [];
    
        foreach ($dominantCodes as $codeIndex => $code) {
            $found = false;
            foreach ($mapping as $wmoCode => $emojiList) {
                $codeList = explode(",", $wmoCode);
                if (in_array($code, $codeList)) {
                    $conditionParts = explode(",", $emojiList);
    
                    // Prepara il gruppo se non esiste
                    if (!isset($groupedConditions[$codeIndex])) {
                        $groupedConditions[$codeIndex] = [];
                    }
    
                    foreach ($conditionParts as $pos => $emoji) {
                        if (!isset($emojiUsage[$emoji])) {
                            // Se mai usata, usala
                            $emojiUsage[$emoji] = [$codeIndex, $pos];
                            $groupedConditions[$codeIndex][$emoji] = $pos;
                        } else {
                            // Confronta priorità: gruppo e posizione
                            list($existingGroup, $existingPos) = $emojiUsage[$emoji];
                            if ($pos < $existingPos || ($pos == $existingPos && $codeIndex < $existingGroup)) {
                                // Emoji ha priorità maggiore ora, spostala
                                unset($groupedConditions[$existingGroup][$emoji]);
                                $groupedConditions[$codeIndex][$emoji] = $pos;
                                $emojiUsage[$emoji] = [$codeIndex, $pos];
                            }
                        }
                    }
    
                    $found = true;
                    break;
                }
            }
    
            if (!$found) {
                // Se il codice non ha mapping, aggiungi gruppo vuoto
                $groupedConditions[$codeIndex] = [];
            }
        }
    
        // Ora costruiamo il risultato finale
        $result = [];
        foreach ($groupedConditions as $group) {
            // Ordina per posizione
            asort($group);
            foreach (array_keys($group) as $emoji) {
                $result[] = $emoji;
            }
            $result[] = ""; // separatore tra gruppi
        }
    
        // Rimuovi l'ultimo separatore se presente
        if (end($result) === "") {
            array_pop($result);
        }
        
        $result = array_filter($result, function($item, $index) use ($result) {
            // Rimuovi "" se è l'inizio, la fine, o se è seguito da un altro ""
            return !($item === "" && (
                $index === 0 ||
                $index === count($result) - 1 ||
                (isset($result[$index + 1]) && $result[$index + 1] === "")
            ));
        }, ARRAY_FILTER_USE_BOTH);        
        // restituisci
        return $result;
    }

    function calculateAccuracy($expectedEmoji, $conditions) {
        $position = 0;
        $accuracyByPosition = [100, 56, 56]; // Posizione 0 = 100%, 1 = 56%, 2 = 56%
    
        foreach ($conditions as $item) {
            if ($item === "") {
                $position = 0;
                continue;
            }
    
            if ($item === $expectedEmoji) {
                return $accuracyByPosition[$position] ?? 0;
            }
    
            $position++;
        }
    
        return 0; // Emoji non trovata
    }
    

    // Filtra i dati per mattina (5:00-12:00) e pomeriggio (12:00-20:00)
    $morningCodes = array_slice($weatherCodes, 5, 7);  // Ore 5:00 - 11:00 (7 valori)
    $afternoonCodes = array_slice($weatherCodes, 12, 9); // Ore 12:00 - 20:00 (9 valori)

    // Determina le condizioni prevalenti (array)
    $morningConditions = getDominantConditions($morningCodes, $validWeatherEmojiFromWmoCode, $wmoCodeToDescEmoji);
    $afternoonConditions = getDominantConditions($afternoonCodes, $validWeatherEmojiFromWmoCode, $wmoCodeToDescEmoji);

    // Mappa inversa (icone → descrizioni) (icone meteo)
    $iconToDescription = array_flip($weatherDescToEmoji);

    // Converti icone in descrizioni testuali
    $morningConditionsText = array_map(function ($icon) use ($iconToDescription) {
        return $iconToDescription[$icon] ?? "";
    }, $morningConditions);

    $afternoonConditionsText = array_map(function ($icon) use ($iconToDescription) {
        return $iconToDescription[$icon] ?? "";
    }, $afternoonConditions);

    $morningAccuracy = 0;
    $afternoonAccuracy = 0;
    
    // Calcola l'accuratezza per la mattina
    $morningAccuracy += calculateAccuracy($weatherDescToEmoji[$morningDesc], $morningConditions);
    
    // Calcola l'accuratezza per il pomeriggio
    $afternoonAccuracy += calculateAccuracy($weatherDescToEmoji[$afternoonDesc], $afternoonConditions);

     // Rimuove elementi vuoti
    $morningConditionsText = array_filter($morningConditionsText, function ($icon) {
        return $icon !== "Sconosciuto";
    });
    $afternoonConditionsText = array_filter($afternoonConditionsText, function ($icon) {
        return $icon !== "Sconosciuto";
    });

    // Risultato finale
    $response = [
        "dominant_conditions" => [
            "morning" => $morningConditionsText,
            "afternoon" => $afternoonConditionsText
        ],
        "accuracy" => [
            "morning" => $morningAccuracy,
            "afternoon" => $afternoonAccuracy,
            "total" => ($morningAccuracy + $afternoonAccuracy)/2
        ]
    ];

    echo json_encode($response);
    exit;
?>
