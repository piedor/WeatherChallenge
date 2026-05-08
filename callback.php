<?php
    require 'assets/dist/google-client/vendor/autoload.php'; // Assicura di installare Google API Client con Composer
    // Include il file per la connessione al database
    include 'utils/db_connection.php';
    // Include il file per la gestione degli errori
    include 'utils/error_handler.php';

    // Sessione sicura e persistente
    require 'utils/session.php';
    header('Cache-Control: private, no-store, max-age=0');

    $client = new Google\Client();
    $client->setClientId($_ENV['GOOGLE_CLIENT_ID']); // Sostituisci con l'ID client di Google
    $client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']); // Sostituisci con il segreto client di Google
    $client->setRedirectUri('https://' . $_SERVER['HTTP_HOST'] . '/StazioneMeteo/dashboard/callback.php'); // Modifica con il tuo URI di callback

    // Ottieni il token di acceso
    if (isset($_GET['code'])) {
        try {
            $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
            $client->setAccessToken($token);

            $oauth2 = new Google\Service\Oauth2($client);
            $userInfo = $oauth2->userinfo->get();

            $email = $userInfo->email;
            $googleId = $userInfo->id;
            $name = $userInfo->name;
            $picture = $userInfo->picture;
            $role = '';

            $adminEmails = [trim($_ENV['ADMIN_EMAIL'])];
            $specialEmails = array_map('trim', explode(',', $_ENV['SPECIAL_EMAILS']));

            // Determina il ruolo in base alla struttura dell'email
            if (in_array($email, $adminEmails)) {
                $role = 'admin';
            } elseif (in_array($email, $specialEmails)) {
                $role = 'professor'; 
            } elseif (preg_match('/^[a-z]+\.[a-z]+@\s*liceodavincitn\.it$/i', $email)) {
                $role = 'professor';
            } elseif (preg_match('/^[a-z]+\.[a-z]+\.\d{2}@\s*liceodavincitn\.it$/i', $email)) {
                $role = 'student';
            } else {
                $role = 'student'; // fallback
            }

            // Inserisce o aggiorna l'utente nel database
            $query = "INSERT INTO users (google_id, email, full_name, role, last_login, picture) 
                    VALUES (?, ?, ?, ?, NOW(), ?)
                    ON DUPLICATE KEY UPDATE 
                        email = VALUES(email), 
                        full_name = VALUES(full_name),
                        role = VALUES(role),
                        last_login = NOW(),
                        picture = VALUES(picture)";
            $stmt = $__con->prepare($query);
            $stmt->bind_param("sssss", $googleId, $email, $name, $role, $picture);
            $stmt->execute();

            // Recupera user_id
            $user_id = $__con->insert_id;
            if ($user_id === 0) {
                $stmt2 = $__con->prepare("SELECT id FROM users WHERE google_id = ?");
                $stmt2->bind_param("s", $googleId);
                $stmt2->execute();
                $user_id = $stmt2->get_result()->fetch_assoc()['id'];
            }

            // Distruggi sessione precedente
            session_unset();
            session_destroy();
            session_start();
            session_regenerate_id(true);

            // Salva i dati dell'utente nella sessione
            $_SESSION['user'] = [
                'google_id' => $userInfo->id,
                'email' => $userInfo->email,
                'name' => $userInfo->name,
                'picture' => $userInfo->picture
            ];

            // Genera e salva remember_token
            $rememberToken = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));

            $stmt3 = $__con->prepare("INSERT INTO remember_tokens 
                          (user_id, token, expires_at, user_agent, ip_address) 
                          VALUES (?, ?, ?, ?, ?)");
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;
            $stmt3->bind_param("issss", $user_id, $rememberToken, $expiry, $user_agent, $ip);
            $stmt3->execute();

            setcookie('remember_token', $rememberToken, [
                'expires'  => time() + 60 * 60 * 24 * 30,
                'path'     => '/',
                'secure'   => true,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            // Reindirizza alla dashboard
            header('Location: index.php');
            exit;
        } catch (Exception $e) {
            redirectToErrorPage(0, 'Errore durante il login: ' . $e->getMessage());
        }
    } else {
        redirectToErrorPage(0, 'Codice di autenticazione non ricevuto.');
    }
?>
