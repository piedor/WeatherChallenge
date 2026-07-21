<?php

    require_once __DIR__ . '/include_bootstrap.php';

?>

<link rel="stylesheet" href="../assets/css/style.css"/>
<!-- PWA - Manifest -->
<link rel="manifest" href="/dashboard/manifest.json">

<!-- Theme Color -->
<meta name="theme-color" content="#f8c63a">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="WeatherChallenge">

<!-- Service Worker Registration -->
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/dashboard/sw.js')
                .then((registration) => {
                    console.log('Service Worker registrato con successo:', registration.scope);
                })
                .catch((error) => {
                    console.log('Registrazione Service Worker fallita:', error);
                });
        });
    }
</script>