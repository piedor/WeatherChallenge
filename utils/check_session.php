<?php
    session_start();

    header('Cache-Control: private, no-store, max-age=0');

    // Rigenera ID sessione per maggiore sicurezza (ogni nuovo accesso)
    if (!isset($_SESSION['regenerated'])) {
        session_regenerate_id(true);
        $_SESSION['regenerated'] = true;
    }

    // Include la connessione al database
    require_once __DIR__ . '/db_connection.php';

    // Include la gestione degli errori
    require_once __DIR__ . '/error_handler.php';

    // Controllo se l'utente è loggato
    if (!isset($_SESSION['user']) || empty($_SESSION['user']['google_id'])) {
        header('Location: /StazioneMeteo/dashboard/login.php');
        exit;
    }

    // Prendi l'utente dalla sessione
    $user = $_SESSION['user'];
    $google_id = $user['google_id'];

    // Query per recuperare i dettagli completi dell'utente
    $query = "SELECT * FROM users WHERE google_id = ?";
    $stmt = $__con->prepare($query);
    $stmt->bind_param("s", $google_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Assegna i dati dell'utente alla variabile $user
        $user = $result->fetch_assoc();
    } else {
        // Se l'utente non esiste, distruggi la sessione e reindirizza al login
        session_destroy();
        header('Location: /StazioneMeteo/dashboard/login.php');
        exit;
    }

    $user_id = $user["id"];
    $role = $user["role"];

    // Imposta un timeout per la sessione
    if (!isset($_SESSION['last_activity'])) {
        $_SESSION['last_activity'] = time();
    } elseif (time() - $_SESSION['last_activity'] > 1800) { // Logout automatico dopo 30 minuti di inattività
        session_destroy();
        header("Location: /StazioneMeteo/dashboard/login.php?message=Sessione scaduta, rieffettua il login.");
        exit;
        
    }
    $_SESSION['last_activity'] = time(); // Aggiorna il tempo dell'ultima attività

?>
