<?php
    // Include il file per la connessione al database
    include 'utils/db_connection.php';

    // Funzione per determinare l'accuratezza della temperatura prevista da quella reale data una soglia
    function calculateTemperatureAccuracy($realTemperature, $predictedTemperature, $threshold = 5.0) {
        // Calcola la differenza assoluta tra temperatura reale e prevista
        $difference = abs($realTemperature - $predictedTemperature);
    
        // Calcola l'accuratezza usando la formula
        $accuracy = max(0, (1 - ($difference / $threshold))) * 100;
    
        return round($accuracy, 2); // Arrotonda a due decimali
    }

    // Recupera la data di ieri
    $yesterday = date('Y-m-d', strtotime('yesterday'));

    // Chiamata API per temperatura giornalieri di ieri
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
    $apiUrl = "$baseUrl/StazioneMeteo/dashboard/api/get_temperatures_station.php?interval=daily&date=" . $yesterday;
    $response = file_get_contents($apiUrl);
    $data = json_decode($response); // Decodifica il JSON
    $realTempAvg = $data->data[3]->values->avg[0]; // Temperatura media
    $realTempMax = $data->data[3]->values->max[0]; // Temperatura massima
    $realTempMin = $data->data[3]->values->min[0]; // Temperatura minima

    // Ottieni i dati meteo dal giorno precedente
    $latitude = "46.0679"; // Inserire la latitudine corretta
    $longitude = "11.1211"; // Inserire la longitudine corretta
    $apiUrl = "https://api.open-meteo.com/v1/forecast?latitude=$latitude&longitude=$longitude&hourly=weather_code&timezone=Europe%2FBerlin&past_days=1&forecast_days=0";

    $response = file_get_contents($apiUrl);
    $data = json_decode($response, true);

    if (!$data || empty($data['hourly']['weather_code'])) {
        die("Errore nel recupero dei dati meteo");
    }

    // Codici meteo
    $hourlyCodes = $data['hourly']['weather_code'];
    $timestamps = $data['hourly']['time'];

    // Salva i codici meteo nel database
    $date = date('Y-m-d', strtotime($timestamps[0])); // Usa la prima data come riferimento
    $weather_codes_json = json_encode($hourlyCodes); // Converti in JSON

    // Inserisci o aggiorna i dati nel database
    $query = "INSERT INTO weather_codes (date, weather_codes) VALUES (?, ?) 
    ON DUPLICATE KEY UPDATE weather_codes = VALUES(weather_codes)";
    $stmt = $__con->prepare($query);
    $stmt->bind_param("ss", $date, $weather_codes_json);
    $stmt->execute();
    $stmt->close();

    // Ottieni le previsioni degli studenti per il giorno precedente
    $yesterday = date('Y-m-d', strtotime('yesterday'));
    $query = "SELECT id, user_id, temperature, morning_desc, afternoon_desc FROM forecasts WHERE date = ?";
    $stmt = $__con->prepare($query);
    $stmt->bind_param("s", $yesterday);
    $stmt->execute();
    $result = $stmt->get_result();

    // Chiamata API per calcolo accuratezza METEO in base ai codici meteo reali e alle previsioni dell'utente
    $apiUrl = "$baseUrl/StazioneMeteo/dashboard/api/calculate_weather_accuracy.php";

    // Array per memorizzare gli user_id con cambiamenti nell'accuratezza
    $userAccurancyChanged = [];

    while ($forecast = $result->fetch_assoc()) {
        // Chiama l'API per l'accuratezza meteo
        $data = [
            "weather_codes" => json_decode($weather_codes_json, true),
            "morning_desc" => $forecast['morning_desc'],
            "afternoon_desc" => $forecast['afternoon_desc']
        ];

        $options = [
            "http" => [
                "header"  => "Content-Type: application/json\r\n",
                "method"  => "POST",
                "content" => json_encode($data)
            ]
        ];
        
        $context  = stream_context_create($options);
        $response = file_get_contents($apiUrl, false, $context);
        
        if ($response === FALSE) {
            die("Errore: errore nella richiesta API per il calcolo delle accuratezze meteo.");
        }
        
        $accuracyData = json_decode($response, true);

        // Calcola accuratezza temperatura
        $tempAccuracy = calculateTemperatureAccuracy($realTempAvg, $forecast['temperature']);

        // Calcola l'accuratezza totale
        $accuracy = round(($accuracyData["accuracy"]["total"] + $tempAccuracy) / 2, 2);

        // Aggiorna i valori nel database solo se c'è una modifica
        $updateQuery = "UPDATE forecasts SET weather_accuracy = ?, temp_accuracy = ?, accuracy = ? WHERE id = ?";
        $updateStmt = $__con->prepare($updateQuery);
        $updateStmt->bind_param("dddi", $accuracyData["accuracy"]["total"], $tempAccuracy, $accuracy, $forecast['id']);
        $updateStmt->execute();

        // Aggiungi l'user_id all'array se non è già presente
        if (!in_array($forecast['user_id'], $userAccurancyChanged)) {
            $userAccurancyChanged[] = $forecast['user_id'];
        }
    }

    // Aggiorna l'affidabilità totale degli utenti che hanno cambiato l'accuratezza
    foreach ($userAccurancyChanged as $userId) {
        $accuracyQuery = "SELECT AVG(accuracy) AS total_accuracy FROM forecasts WHERE user_id = ? AND date <= ?";
        $accuracyStmt = $__con->prepare($accuracyQuery);
        $accuracyStmt->bind_param("is", $userId, $yesterday);
        $accuracyStmt->execute();
        $accuracyResult = $accuracyStmt->get_result();

        if ($accuracyRow = $accuracyResult->fetch_assoc()) {
            $totalAccuracy = round($accuracyRow['total_accuracy'], 2) ?? 0;

            // Aggiorna il campo total_accuracy nella tabella users
            $updateQuery = "UPDATE users SET total_accuracy = ? WHERE id = ?";
            $updateStmt = $__con->prepare($updateQuery);
            $updateStmt->bind_param("di", $totalAccuracy, $userId);
            $updateStmt->execute();
        }
    }

    echo "Calcolo delle accuratezze completato.";
?>
