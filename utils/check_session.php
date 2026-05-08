<?php
    require_once __DIR__ . '/session.php';

    // Include la connessione al database
    require_once __DIR__ . '/db_connection.php';

    // Include la gestione degli errori
    require_once __DIR__ . '/error_handler.php';

    // Se la sessione non è attiva, prova con il remember_token
    if (!isset($_SESSION['user']) || empty($_SESSION['user']['google_id'])) {
        if (isset($_COOKIE['remember_token'])) {
            $token = $_COOKIE['remember_token'];

            $stmt = $__con->prepare("
                SELECT u.* 
                FROM users u 
                JOIN remember_tokens rt ON u.id = rt.user_id 
                WHERE rt.token = ? 
                AND rt.expires_at > NOW()
            ");
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                // Ricrea la sessione
                session_regenerate_id(true);   // importante per sicurezza

                $_SESSION['user'] = [
                    'google_id' => $user['google_id'],
                    'email'     => $user['email'],
                    'name'      => $user['full_name'],
                    'picture'   => $user['picture']
                ];
            } else {
                // Token non valido o scaduto → cancella cookie
                setcookie('remember_token', '', time() - 3600, '/', '', true, true);
                header('Location: /StazioneMeteo/dashboard/login.php');
                exit;
            }
        } else {
            header('Location: /StazioneMeteo/dashboard/login.php');
            exit;
        }
    }

    // Pulizia token scaduti (~1% delle volte)
    if (rand(1, 100) === 1) {
        $__con->query("DELETE FROM remember_tokens WHERE expires_at < NOW()");
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
        setcookie('remember_token', '', time() - 3600, '/');
        header('Location: /StazioneMeteo/dashboard/login.php');
        exit;
    }

    $user_id = $user["id"];
    $role = $user["role"];

    $stmt = $__con->prepare("UPDATE users SET last_active = NOW() WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
?>
