<?php

    /**
     * Gestisci gli errori reindirizzando alle pagine di errore
     */

    require_once __DIR__ . '/settings.php';

    function redirectToErrorPage($code, $message = "Si è verificato un errore.") {
        global $settings;

        // Mappa codici di errore a file specifici
        $errorPages = [
            401 => "notLogged.php",
            403 => "unauthorized.php",
            404 => "notFound.php",
        ];

        // Se il codice di errore ha una pagina dedicata, usala
        if (isset($errorPages[$code])) {
            header("Location: ./error/" . $errorPages[$code]);
        } else {
            // Se in settings.php debugError è attivo allora stampa l'errore
            if(!isset($settings['debugError']) || !$settings['debugError']){
                $message = "Attiva debugError per il messaggio di errore!";
            }

            // Pagina generica di errore
            header("Location: ./error/error.php?message=" . urlencode($message));
        }
        exit;
    }

?>