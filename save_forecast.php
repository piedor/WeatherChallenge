<?php
    // Include il file per il controllo della sessione
    include 'utils/check_session.php';

    // Messaggio di conferma o errore
    $message = $_GET['message'] ?? null;
    $valid_weather_desc = ["Soleggiato", "Parzialmente Nuvoloso", "Nuvoloso", "Pioggia", "Neve", "Grandine", "Temporale"];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $date = $_POST['date'];
        $temp_min = $_POST["temp_min"];
        $temp_max = $_POST["temp_max"];
        $morning_desc = $_POST['morning_desc'];
        $afternoon_desc = $_POST['afternoon_desc'];
        $note = trim($_POST['note']);
        $type = "error";

        // Controllo descrizione meteo valida
        if (!in_array($morning_desc, $valid_weather_desc) || !in_array($afternoon_desc, $valid_weather_desc)) {
            $message = "Descrizione meteo non valida.";
            $type = "error"; // Tipo di messaggio: errore
            header("Location: insert_forecast.php?message=" . urlencode($message) . "&type=" . urlencode($type));
            exit;
        }

        // Controllo della data
        $current_date = new DateTime(); // Data attuale
        $forecast_date = new DateTime($date); // Data inserita dall'utente

        if ($forecast_date <= $current_date) {
            $message = "Non è possibile caricare previsioni per oggi o per giorni passati.";
        } else {
            // Verifica se esiste già una previsione per questa data
            $query = "SELECT COUNT(*) AS count FROM forecasts WHERE user_id = ? AND date = ?";
            $stmt = $__con->prepare($query);
            $stmt->bind_param("is", $user_id, $date);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if ($row['count'] > 0) {
                $message = "Hai già caricato una previsione per questa data.";
            } else {
                // Inserisci una nuova previsione
                $query = "INSERT INTO forecasts (user_id, date, temp_max, temp_min,  morning_desc, afternoon_desc, note) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $__con->prepare($query);
                $stmt->bind_param("issssss", $user_id, $date, $temp_max, $temp_min, $morning_desc, $afternoon_desc, $note);

                if ($stmt->execute()) {
                    $message = "Previsione caricata con successo!";
                    $type = "success";
                    // Aggiungi un giorno alla data della previsione così da riempire in automatico il campo data nel form
                    $forecast_date->modify('+1 day');
                } else {
                    $message = "Errore durante il caricamento della previsione: " . $stmt->error;
                }
            }
        }
        header("Location: insert_forecast.php?message=" . urlencode($message) . "&type=" . urlencode($type) . "&date=" . $forecast_date->format('Y-m-d'));
        exit;
    }
    else{
        header("Location: insert_forecast.php");
        exit;
    }
?>