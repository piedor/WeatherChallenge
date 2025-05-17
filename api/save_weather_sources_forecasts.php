<?php
    // Salva le previsioni del 5 giorno dai siti Meteotrentino ecc...
    include '../utils/db_connection.php';
    // Ritorna il JSON
    header('Content-Type: application/json');

    // Recupera i dati da getMeteoTrentinoForecasts.php
    $forecastJson = file_get_contents('https://liceodavincitn.it/StazioneMeteo/dashboard/api/get_meteo_trentino_forecasts.php');
    $forecastArray = json_decode($forecastJson, true);

    // Controlla se esiste almeno 5 giorni
    if (!isset($forecastArray[4])) {
        die("Errore: 5º giorno non disponibile.");
    }

    echo $forecastArray[4];

    $day = $forecastArray[4];

    // Estrai i dati
    $weatherSourceId = 1; //MeteoTrentino
    $forecastDate = $day['giorno'];
    $morningDesc = $day['mattina'];
    $afternoonDesc = $day['pomeriggio'];
    $tempMax = $day['tMin'];
    $tempMin = $day['tMax'];

    // Placeholder temporanei
    $weatherAccuracy = null;
    $tempAccuracy = null;
    $accuracy = null;
    $tempError = null;

    // Prepara lo statement
    $stmt = $__con->prepare("
        INSERT INTO weather_sources_forecasts (
            weather_source_id,
            date,
            temp_max,
            temp_min,
            morning_desc,
            afternoon_desc,
            weather_accuracy,
            temp_accuracy,
            accuracy,
            temp_error
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        die("Errore nella prepare: " . $__con->error);
    }

    // Tipi: i = intero, s = stringa, d = double
    $stmt->bind_param(
        "issssssddd",
        $weatherSourceId,
        $forecastDate,
        $tempMax,
        $tempMin,
        $morningDesc,
        $afternoonDesc,
        $weatherAccuracy,
        $tempAccuracy,
        $accuracy,
        $tempError
    );

    // Esegui lo statement
    if ($stmt->execute()) {
        echo "Previsione salvata correttamente.";
    } else {
        echo "Errore durante l'inserimento: " . $stmt->error;
    }

    $stmt->close();

    echo json_encode($forecastArray[4]);

?>