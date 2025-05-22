<?php
include '../utils/db_connection.php';
header('Content-Type: application/json');

function insertForecast($con, $url, $weatherSourceId) {
    $forecastJson = file_get_contents($url);

    if ($forecastJson === false) {
        echo json_encode(["error" => "Errore nel recupero dati da $url"]);
        return false;
    }

    $forecastArray = json_decode($forecastJson, true);

    if (!isset($forecastArray[4])) {
        echo json_encode(["error" => "5º giorno non disponibile da $url"]);
        return false;
    }

    $day = $forecastArray[4];

    $forecastDate = $day['giorno'] ?? null;
    $morningDesc = $day['mattina'] ?? null;
    $afternoonDesc = $day['pomeriggio'] ?? null;
    $tempMax = $day['tMax'] ?? null;
    $tempMin = $day['tMin'] ?? null;

    // Validazione base
    if (!$forecastDate || !$morningDesc || !$afternoonDesc || $tempMax === null || $tempMin === null) {
        echo json_encode(["error" => "Dati incompleti per il 5º giorno da $url"]);
        return false;
    }

    $weatherAccuracy = 0;
    $tempAccuracy = 0;
    $accuracy = 0;
    $tempError = 0;

    $stmt = $con->prepare("
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
        echo json_encode(["error" => "Errore prepare: " . $con->error]);
        return false;
    }

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

    $success = $stmt->execute();

    if (!$success) {
        echo json_encode(["error" => "Errore inserimento ($weatherSourceId): " . $stmt->error]);
    }

    $stmt->close();
    return $success;
}

$success1 = insertForecast($__con, 'https://liceodavincitn.it/StazioneMeteo/dashboard/api/get_meteo_trentino_forecasts.php', 1);
$success2 = insertForecast($__con, 'https://liceodavincitn.it/StazioneMeteo/dashboard/api/get_open_meteo_forecasts.php', 2);

if ($success1 && $success2) {
    echo json_encode(["success" => true]);
}
?>