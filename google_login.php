<?php
    require 'assets/dist/google-client/vendor/autoload.php'; // Assicura di installare Google API Client con Composer

    session_start(); 

    header('Cache-Control: private, no-store, max-age=0');

    $client = new Google\Client();
    $client->setClientId('149412784181-0731stjkadfrcgj90to3vm86q72uotil.apps.googleusercontent.com'); // Sostituisci con l'ID client di Google
    $client->setClientSecret('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'); // Sostituisci con il segreto client di Google
    $client->setRedirectUri('https://' . $_SERVER['HTTP_HOST'] . '/StazioneMeteo/dashboard/callback.php'); // Modifica con il tuo URI di callback
    $client->addScope('email');
    $client->addScope('profile');

    // Reindirizza a Google per l'autenticazione
    $authUrl = $client->createAuthUrl();
    
    header('Location: ' . $authUrl);
    exit;
?>
