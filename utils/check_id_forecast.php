<?php
    // Include il file per il controllo della sessione
    include 'utils/check_session.php';
    
    $id = $_GET['id'] ?? null;
    if (!$id) {
        redirectToErrorPage(0, "ID previsione non valido.");
    }

    // Recupera la previsione dell'utente
    $query = "SELECT * FROM forecasts WHERE id = ?";
    $stmt = $__con->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $forecast = $result->fetch_assoc();
    $stmt->close();

    // Verifica che la previsione appartenga all'utente loggato oppure ad uno studente e il professore/admin vuole accederci
    if (!$forecast) {
        redirectToErrorPage(0, "Errore: previsione non trovata.");
    } elseif ($forecast['user_id'] != $user_id && $role !== "professor" && $role !== "admin") {
        redirectToErrorPage(403, "Errore: Non hai i permessi per modificare questa previsione.");
    }
?>