<?php
    require_once 'utils/session.php';
    require_once 'utils/db_connection.php';

    // Elimina il remember_token dal DB
    if (isset($_COOKIE['remember_token'])) {
        $token = $_COOKIE['remember_token'];
        $stmt = $__con->prepare("DELETE FROM remember_tokens WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        setcookie('remember_token', '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'secure'   => true,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }
    
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
