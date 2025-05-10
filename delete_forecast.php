<?php
    // Include il file per il controllo della sessione e che la previsione appartenga all'utente corrente
    include 'utils/check_id_forecast.php';

    // Messaggio di conferma o errore
    $message = $_GET['message'] ?? null;

    // Elimina la previsione
    $delete_query = "DELETE FROM forecasts WHERE id = ?";
    $delete_stmt = $__con->prepare($delete_query);
    $delete_stmt->bind_param("i", $id);
    if ($delete_stmt->execute()) {
        $type = "success";
        $message = "Previsione eliminata con successo.";
    } else {
        $message = "Errore durante l'eliminazione della previsione.";
    }
    $delete_stmt->close();

    // Reindirizza alla dashboard con un messaggio
    header("Location: history_forecast.php?message=" . urlencode($message) . "&type=" . urlencode($type));
    exit;
?>
