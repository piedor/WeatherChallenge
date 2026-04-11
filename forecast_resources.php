<?php
    include 'utils/check_session.php';
    $message = $_GET['message'] ?? null;
    $type    = $_GET['type'] ?? null;
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Strumenti Meteo</title>
    <meta name="description" content="WebApp previsioni meteo">
    <meta name="author" content="Pietro Dorighi">
    <link href="./favicon.ico" rel="shortcut icon" type="image/vnd.microsoft.icon">
    <?php require_once './utils/style.php'; ?>
    <link rel="stylesheet" href="./assets/css/style_app.css?v=<?php echo filemtime('assets/css/style_app.css'); ?>">
    <link rel="stylesheet" href="./assets/css/style_forecast_resources.css?v=<?php echo filemtime('assets/css/style_forecast_resources.css'); ?>">
</head>
<body class="bg-light">
    <?php require('./utils/header.php'); ?>

    <?php if (!empty($message)):
        $alertClass = ($type === "success") ? "alert-success" : "alert-danger"; ?>
        <div id="messageAlert" class="alert <?= $alertClass ?> alert-dismissible fade show mx-auto" role="alert" style="max-width:1200px;">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi"></button>
        </div>
    <?php endif; ?>

    <div class="container">
        <h2 class="text-center mt-3">🛠️ Strumenti per le Previsioni Meteo</h2>
        <p class="text-center text-muted">Tocca uno strumento per aprirlo direttamente.</p>

        <div class="tools-grid">

            <!-- SPAGHETTI — in evidenza -->
            <a href="https://www.wetterzentrale.de/ens_image.php?model=gfs&member=ENS&geoid=70934&bw=1&var=201"
               target="_blank" rel="noopener" class="tool-card featured">
                <div class="tool-card-banner" style="background:#0d6efd;"></div>
                <div class="tool-card-body">
                    <div class="tool-card-icon">🍝</div>
                    <div>
                        <p class="tool-card-title">Spaghetti Meteo — Trento</p>
                        <span class="featured-badge">⭐ Più usato</span>
                    </div>
                    <p class="tool-card-desc">Grafico ensemble GFS a 850 hPa per Trento. Mostra la dispersione dei modelli per valutare l'incertezza della previsione.</p>
                    <span class="tool-card-cta">Apri su Wetterzentrale →</span>
                </div>
            </a>

            <!-- MODELLI GFS -->
            <a href="https://www.wetterzentrale.de/de/topkarten.php?model=gfs&lid=OP"
               target="_blank" rel="noopener" class="tool-card">
                <div class="tool-card-banner" style="background:#198754;"></div>
                <div class="tool-card-body">
                    <div class="tool-card-icon">🌍</div>
                    <p class="tool-card-title">Mappe GFS — Wetterzentrale</p>
                    <p class="tool-card-desc">Mappe sinottiche e modelli numerici GFS aggiornati. Utile per analizzare pattern di pressione e fronti.</p>
                    <span class="tool-card-cta">Apri su Wetterzentrale →</span>
                </div>
            </a>

            <!-- WINDY -->
            <a href="https://www.windy.com/?46.067,11.121,8"
               target="_blank" rel="noopener" class="tool-card">
                <div class="tool-card-banner" style="background:#20c997;"></div>
                <div class="tool-card-body">
                    <div class="tool-card-icon">🌬️</div>
                    <p class="tool-card-title">Windy — Vento e Meteo</p>
                    <p class="tool-card-desc">Mappa interattiva del vento, pioggia, temperatura e nuvole in tempo reale. Centrata su Trento.</p>
                    <span class="tool-card-cta">Apri su Windy →</span>
                </div>
            </a>

            <!-- RADAR METEONETWORK -->
            <a href="https://www.meteonetwork.it/rete/livemap/"
               target="_blank" rel="noopener" class="tool-card">
                <div class="tool-card-banner" style="background:#fd7e14;"></div>
                <div class="tool-card-body">
                    <div class="tool-card-icon">📡</div>
                    <p class="tool-card-title">Radar — MeteoNetwork</p>
                    <p class="tool-card-desc">Rete di stazioni meteo amatoriali con dati in tempo reale: temperatura, pioggia, vento da stazioni locali.</p>
                    <span class="tool-card-cta">Apri su MeteoNetwork →</span>
                </div>
            </a>

            <!-- TEMPORALI ESTOFEX -->
            <a href="http://www.estofex.org/"
               target="_blank" rel="noopener" class="tool-card">
                <div class="tool-card-banner" style="background:#dc3545;"></div>
                <div class="tool-card-body">
                    <div class="tool-card-icon">⛈️</div>
                    <p class="tool-card-title">Estofex — Temporali</p>
                    <p class="tool-card-desc">Bollettini di allerta temporali per l'Europa. Fondamentale per prevedere eventi convettivi intensi.</p>
                    <span class="tool-card-cta">Apri su Estofex →</span>
                </div>
            </a>

            <!-- METEOTRENTINO -->
            <a href="https://www.meteotrentino.it/"
               target="_blank" rel="noopener" class="tool-card">
                <div class="tool-card-banner" style="background:#6f42c1;"></div>
                <div class="tool-card-body">
                    <div class="tool-card-icon">🏔️</div>
                    <p class="tool-card-title">MeteoTrentino</p>
                    <p class="tool-card-desc">Previsioni ufficiali e dati meteo per la provincia di Trento. Ottimo riferimento per confrontare le proprie previsioni.</p>
                    <span class="tool-card-cta">Apri su MeteoTrentino →</span>
                </div>
            </a>

        </div>
    </div>

    <script src="./assets/js/main.js?v=2"></script>
</body>
</html>
