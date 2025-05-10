<?php
    // Include il file per la connessione al database
    include '../utils/db_connection.php';

    // chiavi HMAC stazione Da Vinci
    $public_key = "8744e62ed3f4eac54a2ceb1eca311cf5e028f477cb2322bc";
    $private_key = "f0e049711fb482dad98e9172045979a0f114ea5719b6c353";

    $id_stazione = "03A0F735";
    $method = "GET";

    // Controlla se è stata passata una data e l'intervallo come parametro GET
    if (!isset($_GET['interval'])) {
        echo json_encode(["error" => "Parametro 'interval' richiesto (Formato: YYYY-MM-DD)"]);
        exit;
    }
    if (!isset($_GET['date'])) {
        echo json_encode(["error" => "Parametro 'date' richiesto (Formato: YYYY-MM-DD)"]);
        exit;
    }

    // Richiesta dati di $date dalle 00:00 alle 24:00
    $date = $_GET['date'];
    $tz = new DateTimeZone('UTC'); // imposta la tua timezone locale

    // Inizio del giorno alle 00:00 ora locale
    $start = new DateTime($date . ' 00:00:00', $tz);
    $unixMidnightStart = $start->getTimestamp();

    // Fine del giorno alle 23:00
    $end = new DateTime($date . ' 23:00:00', $tz);
    $unixMidnightEnd = $end->getTimestamp();

    $intervalloTemp = $_GET['interval'];

    $request = "/data/" . $id_stazione . "/" . $intervalloTemp . "/from/" . $unixMidnightStart . "/to/" . $unixMidnightEnd;
    
    //// Richiesta al sito fieldclimate ////
    //*********************************************//
    // Data con formato RFC2616
    $timestamp = gmdate('D, d M Y H:i:s T');
    // Creating content to sign with private key
    $content_to_sign = $method . $request . $timestamp . $public_key;
    // Hash content to sign into HMAC signature
    $signature = hash_hmac("sha256", $content_to_sign, $private_key);
    // Add required headers
    // Authorization: hmac public_key:signature
    // Date: Wed, 25 Nov 2014 12:45:26 GMT
    $headers = [
        "Accept: application/json",
        "Authorization: hmac {$public_key}:{$signature}",
        "Date: {$timestamp}"
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.fieldclimate.com/v2" . $request);
    // SSL important
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    if ($method == 'POST') {
        curl_setopt($ch, CURLOPT_POST, 1);
    }
    //********************************************//
    
    // Risposta con dati
    $risposta = curl_exec($ch);
    // Chiudi connessione
    curl_close($ch);
    // Ritorna dati in JSON

    echo $risposta;
?>