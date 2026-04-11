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
    <title>Inserisci Previsione Meteo</title>
    <meta name="description" content="WebApp previsioni meteo">
    <meta name="author" content="Pietro Dorighi">
    <link href="./favicon.ico" rel="shortcut icon" type="image/vnd.microsoft.icon">
    <?php require_once './utils/style.php'; ?>
    <link rel="stylesheet" href="./assets/css/style_app.css?v=<?php echo filemtime('assets/css/style_app.css'); ?>">
    <link rel="stylesheet" href="./assets/css/style_dashboard.css?v=<?php echo filemtime('assets/css/style_dashboard.css'); ?>">
    <link rel="stylesheet" href="./assets/css/style_insert_forecast.css?v=<?php echo filemtime('assets/css/style_insert_forecast.css'); ?>">
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
        <h1 class="page-title text-center mb-4">Inserisci la tua Previsione Meteo</h1>

        <form id="forecastForm" method="POST" action="save_forecast.php">

            <!-- Data -->
            <div class="mb-4">
                <label for="date" class="form-label">Data:</label>
                <input type="date" id="date" name="date" class="form-control" required>
            </div>

            <div class="row g-3">

                <!-- Colonna bottoni -->
                <div class="col-md-8">

                    <!-- Mattina -->
                    <div class="mb-4">
                        <label class="form-label">Descrizione Meteo — Mattina:</label>
                        <div class="weather-group">
                            <button type="button" class="weather-btn" data-value="Soleggiato"            data-bs-toggle="tooltip" title="Soleggiato"            onclick="selectWeather(this,'morning_desc')">☀️</button>
                            <button type="button" class="weather-btn" data-value="Parzialmente Nuvoloso" data-bs-toggle="tooltip" title="Parzialmente Nuvoloso" onclick="selectWeather(this,'morning_desc')">⛅</button>
                            <button type="button" class="weather-btn" data-value="Nuvoloso"              data-bs-toggle="tooltip" title="Nuvoloso"              onclick="selectWeather(this,'morning_desc')">☁️</button>
                            <button type="button" class="weather-btn" data-value="Pioggia"               data-bs-toggle="tooltip" title="Pioggia"               onclick="selectWeather(this,'morning_desc')">🌧️</button>
                            <button type="button" class="weather-btn" data-value="Neve"                  data-bs-toggle="tooltip" title="Neve"                  onclick="selectWeather(this,'morning_desc')">❄️</button>
                            <button type="button" class="weather-btn" data-value="Grandine"              data-bs-toggle="tooltip" title="Grandine"              onclick="selectWeather(this,'morning_desc')">⚽</button>
                            <button type="button" class="weather-btn" data-value="Temporale"             data-bs-toggle="tooltip" title="Temporale"             onclick="selectWeather(this,'morning_desc')">🌩️</button>
                        </div>
                        <input type="hidden" id="morning_desc" name="morning_desc" required>
                    </div>

                    <!-- Pomeriggio -->
                    <div class="mb-4">
                        <label class="form-label">Descrizione Meteo — Pomeriggio:</label>
                        <div class="weather-group">
                            <button type="button" class="weather-btn" data-value="Soleggiato"            data-bs-toggle="tooltip" title="Soleggiato"            onclick="selectWeather(this,'afternoon_desc')">☀️</button>
                            <button type="button" class="weather-btn" data-value="Parzialmente Nuvoloso" data-bs-toggle="tooltip" title="Parzialmente Nuvoloso" onclick="selectWeather(this,'afternoon_desc')">⛅</button>
                            <button type="button" class="weather-btn" data-value="Nuvoloso"              data-bs-toggle="tooltip" title="Nuvoloso"              onclick="selectWeather(this,'afternoon_desc')">☁️</button>
                            <button type="button" class="weather-btn" data-value="Pioggia"               data-bs-toggle="tooltip" title="Pioggia"               onclick="selectWeather(this,'afternoon_desc')">🌧️</button>
                            <button type="button" class="weather-btn" data-value="Neve"                  data-bs-toggle="tooltip" title="Neve"                  onclick="selectWeather(this,'afternoon_desc')">❄️</button>
                            <button type="button" class="weather-btn" data-value="Grandine"              data-bs-toggle="tooltip" title="Grandine"              onclick="selectWeather(this,'afternoon_desc')">⚽</button>
                            <button type="button" class="weather-btn" data-value="Temporale"             data-bs-toggle="tooltip" title="Temporale"             onclick="selectWeather(this,'afternoon_desc')">🌩️</button>
                        </div>
                        <input type="hidden" id="afternoon_desc" name="afternoon_desc" required>
                    </div>

                </div>

                <!-- Colonna legenda -->
                <div class="col-md-4 mb-3">

                    <!-- Toggle visibile solo su mobile -->
                    <button type="button" class="legend-toggle" id="legendToggle">
                        <span>📖 Legenda Meteo</span>
                        <span class="arrow">▼</span>
                    </button>

                    <!-- Card legenda -->
                    <div class="legend-card card shadow-sm" id="legendCard">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0">Legenda Meteo</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li>☀️ Soleggiato</li>
                                <li>⛅ Parzialmente Nuvoloso</li>
                                <li>☁️ Nuvoloso</li>
                                <li>🌧️ Pioggia</li>
                                <li>❄️ Neve</li>
                                <li>⚽ Grandine (misto pioggia)</li>
                                <li>🌩️ Temporale (misto pioggia)</li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div><!-- /row -->

            <!-- Temperature affiancate -->
            <div class="temp-row mb-4">
                <div>
                    <label for="temp_min" class="form-label text-primary"><span class="d-md-none">Temp Min (°C):</span><span class="d-none d-md-inline">Temperatura Minima (°C):</span></label>
                    <input type="number" id="temp_min" name="temp_min" step="0.1" class="form-control" value="0" required>
                </div>
                <div>
                    <label for="temp_max" class="form-label text-danger"><span class="d-md-none">Temp Max (°C):</span><span class="d-none d-md-inline">Temperatura Massima (°C):</span></label>
                    <input type="number" id="temp_max" name="temp_max" step="0.1" class="form-control" value="0" required>
                </div>
            </div>

            <!-- Nota -->
            <div class="mb-3">
                <label for="note" class="form-label">Nota (facoltativa):</label>
                <textarea class="form-control" id="note" name="note" rows="3"
                    placeholder="Inserisci un'allerta meteo o una nota rilevante..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary w-100 mb-4">Salva Previsione</button>
        </form>
    </div>

    <script>
        // Auto-chiudi alert
        setTimeout(function() {
            const alertBox = document.getElementById('messageAlert');
            if (alertBox) new bootstrap.Alert(alertBox).close();
        }, 3000);

        // Selezione meteo
        function selectWeather(button, inputId) {
            button.parentElement.querySelectorAll('.weather-btn')
                  .forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            document.getElementById(inputId).value = button.getAttribute('data-value');
        }

        // Toggle legenda — listener separato, non inline
        document.getElementById('legendToggle').addEventListener('click', function() {
            this.classList.toggle('open');
            document.getElementById('legendCard').classList.toggle('visible');
        });

        // Validazione form
        document.getElementById('forecastForm').addEventListener('submit', function(e) {
            const morning   = document.getElementById('morning_desc').value;
            const afternoon = document.getElementById('afternoon_desc').value;
            if (!morning || !afternoon) {
                e.preventDefault();
                alert('Per favore, seleziona una descrizione per il mattino e il pomeriggio.');
            }
        });

        // Validazione temperature
        document.getElementById('forecastForm').addEventListener('submit', function(e) {
            const tempMin = parseFloat(document.getElementById('temp_min').value);
            const tempMax = parseFloat(document.getElementById('temp_max').value);
            if (tempMin > tempMax) {
                e.preventDefault();
                alert('Errore: La temperatura minima non può essere maggiore della massima.');
            }
        });

        // Pre-compila data dall'URL
        document.addEventListener("DOMContentLoaded", function() {
            const dateParam = new URLSearchParams(window.location.search).get("date");
            if (dateParam) document.getElementById('date').value = dateParam;
        });
    </script>
    <script src="./assets/js/main.js?v=2"></script>
</body>
</html>
