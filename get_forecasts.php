<?php
    // Include il file per la connessione al database
    include 'utils/db_connection.php';
    // Include il file per la gestione degli errori
    include 'utils/error_handler.php';

    // Recupera le previsioni degli utenti per i prossimi 5 giorni, ordinati per affidabilità totale, 
    // prendi solo gli utenti con 5 previsioni con affidabilità calcolata
    $query = "WITH forecast_counts AS (
        SELECT user_id, COUNT(*) AS total_valid_forecasts
        FROM forecasts
        WHERE accuracy != 0 AND accuracy IS NOT NULL
        GROUP BY user_id
    ),
    ranked_forecasts AS (
        SELECT 
            DATE_FORMAT(f.date, '%d/%m/%Y') AS date, 
            f.date AS date2,
            f.temp_max, 
            f.temp_min, 
            f.morning_desc, 
            f.afternoon_desc, 
            f.note,
            u.full_name,
            u.total_accuracy,
            u.score,
            u.role,
            fc.total_valid_forecasts,
            ROW_NUMBER() OVER (PARTITION BY f.user_id ORDER BY f.date ASC) AS rn
        FROM forecasts f
        JOIN users u ON f.user_id = u.id
        JOIN forecast_counts fc ON fc.user_id = f.user_id
        WHERE f.date >= CURDATE()
        AND fc.total_valid_forecasts >= 5
    )
    SELECT date, date2, temp_max, temp_min, morning_desc, afternoon_desc, note, full_name, total_accuracy, score, role
    FROM ranked_forecasts
    WHERE rn <= 5
    ORDER BY score DESC, full_name ASC, date2 ASC;";  

    $stmt = $__con->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $forecasts = $result->fetch_all(MYSQLI_ASSOC);

    // Restituisce i dati in formato JSON
    header('Content-Type: application/json');

    if (!empty($forecasts)) {
        echo json_encode($forecasts);
    } else {
        // Fallback: previsioni Open-Meteo salvate nel DB (weather_source_id = 2)
        $fallback_query = "SELECT 
            DATE_FORMAT(date, '%d/%m/%Y') AS date,
            temp_max,
            temp_min,
            morning_desc,
            afternoon_desc,
            NULL AS note,
            'Open-Meteo' AS full_name,
            NULL AS total_accuracy,
            NULL AS score,
            'source' AS role
        FROM weather_sources_forecasts
        WHERE weather_source_id = 2
          AND date >= CURDATE()
        ORDER BY date ASC
        LIMIT 5";

        $fallback_stmt = $__con->prepare($fallback_query);
        $fallback_stmt->execute();
        $fallback_result = $fallback_stmt->get_result();
        $fallback_forecasts = $fallback_result->fetch_all(MYSQLI_ASSOC);

        echo json_encode($fallback_forecasts);
    }
?>