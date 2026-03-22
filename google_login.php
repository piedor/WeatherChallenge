<?php
    require 'assets/dist/google-client/vendor/autoload.php'; // Assicura di installare Google API Client con Composer
    require_once __DIR__ . '/lib/dotenv/vendor/autoload.php';

    session_start(); 

    header('Cache-Control: private, no-store, max-age=0');

    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    $client = new Google\Client();
    $client->setClientId($_ENV['GOOGLE_CLIENT_ID']); // Sostituisci con l'ID client di Google
    $client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']); // Sostituisci con il segreto client di Google
    $client->setRedirectUri('https://' . $_SERVER['HTTP_HOST'] . '/StazioneMeteo/dashboard/callback.php'); // Modifica con il tuo URI di callback
    $client->addScope('email');
    $client->addScope('profile');

    // Reindirizza a Google per l'autenticazione
    $authUrl = $client->createAuthUrl();
    
    header('Location: ' . $authUrl);
    exit;
?>
