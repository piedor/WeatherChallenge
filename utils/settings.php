<?php
    /**
     * Importa le impostazioni
     */

    // Percorso del file di configurazione JSON
    $configFile = __DIR__ . '/../StazioneMeteo.json';

    // Controlla se il file esiste prima di leggerlo
    if (!file_exists($configFile)) {
        die("Errore: Il file di configurazione non è stato trovato.");
    }

    // Legge il file JSON
    $jsonContent = file_get_contents($configFile);

    // Decodifica il JSON in un oggetto PHP
    $settings = json_decode($jsonContent, true);

    // Controllo se la decodifica è riuscita
    if (json_last_error() !== JSON_ERROR_NONE) {
        die("Errore nella lettura del file JSON: " . json_last_error_msg());
    }

    // Icone meteo (descrizione a emoji)
    $weatherDescToEmoji = $settings['weatherDescToEmoji'] ?? [];

    // Codice meteo (openMeteo) a array[Descrizione, emoji]
    $wmoCodeToDescEmoji = $settings['wmoCodeToDescEmoji'] ?? [];

    // Icone valide per ogni singolo codice meteo (per previsioni utenti)
    $validWeatherEmojiFromWmoCode = $settings['validWeatherEmojiFromWmoCode'] ?? [];

    // URL base
    $baseUrl = $settings['baseUrl'] ?? "";

    // Includi la versione per evitare problemi di cache
    require_once 'version.php';

?>