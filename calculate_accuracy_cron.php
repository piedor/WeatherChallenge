<?php
    // Include il file per la connessione al database
    include 'utils/db_connection.php';

    // Funzione per determinare l'accuratezza della temperatura prevista da quella reale data una soglia
    function calculateTemperatureAccuracy($forecastMin, $forecastMax, $realMin, $realMax, $threshold = 10.0) {
        $errorMin = abs($forecastMin - $realMin);
        $errorMax = abs($forecastMax - $realMax);
        
        $maxError = 10; // Oltre i 10°C l'accuratezza è 0
        $accuracyMin = max(0, 100 - ($errorMin / $threshold) * 100);
        $accuracyMax = max(0, 100 - ($errorMax / $threshold) * 100);
        
        return round(($accuracyMin + $accuracyMax) / 2, 2); // Media delle due accuratezze
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
    $query = "SELECT id, user_id, temp_max, temp_min, morning_desc, afternoon_desc FROM forecasts WHERE date = ?";
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
        $tempAccuracy = calculateTemperatureAccuracy($forecast['temp_min'], $forecast['temp_max'], $realTempMin, $realTempMax);

        // Calcola la media degli errori assoluti tra temp min e max prevista e reale
        $errorTempMin = abs($forecast['temp_min'] - $realTempMin);
        $errorTempMax = abs($forecast['temp_max'] - $realTempMax);

        $errorTempAvg = round(($errorTempMax + $errorTempMin) / 2, 1);

        // Calcola l'accuratezza totale
        $accuracy = round(($accuracyData["accuracy"]["total"] * 0.6 + $tempAccuracy * 0.4), 2);

        // Aggiorna i valori nel database solo se c'è una modifica
        $updateQuery = "UPDATE forecasts SET weather_accuracy = ?, temp_accuracy = ?, accuracy = ?, temp_error = ? WHERE id = ?";
        $updateStmt = $__con->prepare($updateQuery);
        $updateStmt->bind_param("ddddi", $accuracyData["accuracy"]["total"], $tempAccuracy, $accuracy, $errorTempAvg, $forecast['id']);
        $updateStmt->execute();

        // Aggiungi l'user_id all'array se non è già presente
        if (!in_array($forecast['user_id'], $userAccurancyChanged)) {
            $userAccurancyChanged[] = $forecast['user_id'];
        }
    }

    // Aggiorna l'affidabilità totale degli utenti che hanno cambiato l'accuratezza
    $oneMonthAgo = date('Y-m-d', strtotime('-2 month', strtotime($yesterday)));
    foreach ($userAccurancyChanged as $userId) {
        $accuracyQuery = "SELECT AVG(accuracy) AS total_accuracy FROM forecasts WHERE user_id = ? AND date BETWEEN ? AND ?";
        $accuracyStmt = $__con->prepare($accuracyQuery);
        $accuracyStmt->bind_param("iss", $userId, $oneMonthAgo, $yesterday);
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

    // Aggiorna lo score di tutti gli utenti (comprende bonus)
    $query = "
        SELECT id
        FROM users";

    $stmt = $__con->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $usersId = [];

    while ($row = $result->fetch_assoc()) {
        $usersId[] = $row['id'];
    }

    foreach ($usersId as $userId) {
        // BONUS COSTANZA – ultimi 30 giorni
        $bonusQuery = "
            SELECT DISTINCT DATE(date) AS forecast_date
            FROM forecasts 
            WHERE user_id = ? 
            AND date BETWEEN DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND CURDATE()
            ORDER BY date ASC
        ";

        $stmt = $__con->prepare($bonusQuery);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $dates = [];
        while ($row = $result->fetch_assoc()) {
            $dates[] = $row['forecast_date'];
        }

        $maxConsecutive = 0;
        $currentStreak = 0;
        $prevDate = null;

        foreach ($dates as $index => $date) {
            $current = new DateTime($date);

            if ($index === 0) {
                $prevDate = $current;
                continue;
            }

            $diff = $prevDate->diff($current)->days;

            if ($diff === 1) {
                $currentStreak++;
            } else {
                $maxConsecutive = max($maxConsecutive, $currentStreak);
                $currentStreak = 0;
            }

            $prevDate = $current;
        }

        // Check last streak
        $maxConsecutive = max($maxConsecutive, $currentStreak);

        // Calcolo proporzionale su max 25 giorni
        $consistencyBonus = round(min($maxConsecutive, 25) / 25 * 5, 2);

        // Salva nel DB
        $bonusUpdate = "UPDATE users SET consistency_bonus = ? WHERE id = ?";
        $stmt = $__con->prepare($bonusUpdate);
        $stmt->bind_param("di", $consistencyBonus, $userId);
        $stmt->execute();

        // Recupera tutte le previsioni valutate con accuratezza >= 60 (corrette), ordinate per data, negli ultimi 30 giorni
        $query = "
        SELECT DATE(date) AS forecast_date 
        FROM forecasts 
        WHERE user_id = ? 
        AND accuracy >= 60 
        AND date BETWEEN DATE_SUB(CURDATE(), INTERVAL 20 DAY) AND CURDATE()
        ORDER BY date ASC
        ";

        $stmt = $__con->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $dates = [];
        while ($row = $result->fetch_assoc()) {
            $dates[] = $row['forecast_date'];
        }

        // Calcolo serie positiva
        $streak = 1;
        $maxStreak = 0;
        $prevDate = null;

        foreach ($dates as $date) {
            $current = new DateTime($date);

            if ($prevDate) {
                $interval = $prevDate->diff($current)->days;
                if ($interval === 1) {
                    $streak++;
                } else {
                    $streak = 1;
                }
            }

            $maxStreak = max($maxStreak, $streak);
            $prevDate = $current;
        }

        // Calcolo del bonus: Se la serie massima è 15 giorni o più, ottieni il massimo: 5.0
        $positiveSeriesBonus = round(min($maxStreak, 15) / 15 * 5, 2);

        // Salva il bonus nel database
        $update = "UPDATE users SET positive_series_bonus = ? WHERE id = ?";
        $stmt = $__con->prepare($update);
        $stmt->bind_param("di", $positiveSeriesBonus, $userId);
        $stmt->execute();

        // Bonus previsioni corrette con caricamento in anticipo

        $query = "
            SELECT date, updated_at
            FROM forecasts
            WHERE user_id = ?
            AND accuracy >= 60
            AND date BETWEEN DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND CURDATE()
        ";

        $stmt = $__con->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $total = 0;
        $early = 0;

        while ($row = $result->fetch_assoc()) {
            $forecastDate = new DateTime($row['date']);
            $updatedAt = new DateTime($row['updated_at']);

            $daysBefore = (int)$updatedAt->diff($forecastDate)->format('%a');

            $total++;
            if ($daysBefore >= 4) {
                $early++;
            }
        }

        $earlyForecastsBonus = 0.0;
        if ($total > 0) {
            $earlyForecastsBonus = round(($early / $total) * 5, 2); // max 5%
        }

        $earlyForecastsBonus = min($earlyForecastsBonus, 5.0); // Massimo 5% complessivo
        // Salva il bonus nel database
        $update = "UPDATE users SET early_forecasts_bonus = ? WHERE id = ?";
        $stmt = $__con->prepare($update);
        $stmt->bind_param("di", $earlyForecastsBonus, $userId);
        $stmt->execute();

        
        $score = min(100, $totalAccuracy + $consistencyBonus + $positiveSeriesBonus + $earlyForecastsBonus);

        // Aggiorna il campo score nella tabella users
        $updateQuery = "UPDATE users SET score = LEAST(100.00, ROUND(`consistency_bonus` + `positive_series_bonus` + `early_forecasts_bonus` + `total_accuracy`, 2)) WHERE id = ?";
        $updateStmt = $__con->prepare($updateQuery);
        $updateStmt->bind_param("i", $userId);
        $updateStmt->execute();
    }

    // Calcola accuratezza meteo ufficiali
    $yesterday = date('Y-m-d', strtotime('yesterday'));
    $query = "SELECT * FROM weather_sources_forecasts WHERE date = ?";
    $stmt = $__con->prepare($query);
    $stmt->bind_param("s", $yesterday);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        // Chiama l'API per l'accuratezza meteo
        $data = [
            "weather_codes" => json_decode($weather_codes_json, true),
            "morning_desc" => $row['morning_desc'],
            "afternoon_desc" => $row['afternoon_desc']
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
        $tempAccuracy = calculateTemperatureAccuracy($row['temp_min'], $row['temp_max'], $realTempMin, $realTempMax);

        // Calcola l'accuratezza totale
        $accuracy = round(($accuracyData["accuracy"]["total"] * 0.6 + $tempAccuracy * 0.4), 2);

        // Calcola la media degli errori assoluti tra temp min e max prevista e reale
        $errorTempMin = abs($row['temp_min'] - $realTempMin);
        $errorTempMax = abs($row['temp_max'] - $realTempMax);

        $errorTempAvg = round(($errorTempMax + $errorTempMin) / 2, 1);

        // Aggiorna i valori nel database
        $updateQuery = "UPDATE weather_sources_forecasts SET weather_accuracy = ?, temp_accuracy = ?, accuracy = ?, temp_error = ? WHERE id = ?";
        $updateStmt = $__con->prepare($updateQuery);
        $updateStmt->bind_param("ddddi", $accuracyData["accuracy"]["total"], $tempAccuracy, $accuracy, $errorTempAvg, $row['id']);
        $updateStmt->execute();
    }

    echo "Calcolo delle accuratezze completato.";
?>
