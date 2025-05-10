<?php
    session_start();

    header('Cache-Control: private, no-store, max-age=0');

    require 'assets/dist/google-client/vendor/autoload.php'; // Assicura di installare Google API Client con Composer
    // Include il file per la connessione al database
    include 'utils/db_connection.php';
    // Include il file per la gestione degli errori
    include 'utils/error_handler.php';

    $client = new Google\Client();
    $client->setClientId('149412784181-0731stjkadfrcgj90to3vm86q72uotil.apps.googleusercontent.com'); // Sostituisci con l'ID client di Google
    $client->setClientSecret('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'); // Sostituisci con il segreto client di Google
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
            $role = '';

            $adminEmails = ['pietro.dorighi@liceodavincitn.it'];

            // Determina il ruolo in base alla struttura dell'email
            if (in_array($email, $adminEmails)) {
                $role = 'admin';
            } elseif (preg_match('/^[a-z]+\.[a-z]+@\s*liceodavincitn\.it$/i', $email)) {
                $role = 'professor';
            } elseif (preg_match('/^[a-z]+\.[a-z]+\.\d{2}@\s*liceodavincitn\.it$/i', $email)) {
                $role = 'student';
            } else {
                $role = 'student'; // fallback
            }

            // Inserisce o aggiorna l'utente nel database
            $query = "INSERT INTO users (google_id, email, full_name, role) 
                    VALUES (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                        email = VALUES(email), 
                        full_name = VALUES(full_name),
                        role = VALUES(role)";
            $stmt = $__con->prepare($query);
            $stmt->bind_param("ssss", $googleId, $email, $name, $role);
            $stmt->execute();

            // Salva i dati dell'utente nella sessione
            $_SESSION['user'] = [
                'google_id' => $userInfo->id,
                'email' => $userInfo->email,
                'name' => $userInfo->name,
                'picture' => $userInfo->picture
            ];

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
