<?php
    session_start();
    session_unset();  // Rimuove tutte le variabili di sessione
    session_destroy();  // Distrugge la sessione attuale
    // Cancella il cookie della sessione
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }    
    header('Location: login.php');
    exit;
?>
