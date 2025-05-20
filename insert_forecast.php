<?php
    // Include il file per il controllo della sessione
    include 'utils/check_session.php';

    // Messaggio di conferma o errore
    $message = $_GET['message'] ?? null;
    $type = $_GET['type'] ?? null; // "success" o "error"

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Previsioni Meteo</title>
    <meta name="description" content="WebApp previsioni meteo">
    <meta name="author" content="Pietro Dorighi">
    <link href="./favicon.ico" rel="shortcut icon" type="image/vnd.microsoft.icon">
    <?php require_once './utils/style.php'; ?>
    <link rel="stylesheet" href="./assets/css/style_app.css">
    <link rel="stylesheet" href="./assets/css/style_dashboard.css">
</head>
<body class="bg-light">
    <?php require ('./utils/header.php'); ?>
    <!-- Eventuali messaggi di errore/successo -->
    <?php if (!empty($message)): 
            $alertClass = ($type === "success") ? "alert-success" : "alert-danger";
    ?>
        <div id="messageAlert" class="alert <?= $alertClass ?> alert-dismissible fade show mx-auto" role="alert" style="max-width: 1200px;">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi"></button>
        </div>
    <?php endif; ?>
    <div class="container">
        <h1 class="text-center mb-4">Inserisci la tua Previsione Meteo</h1>
        <form id="forecastForm" method="POST" action="save_forecast.php">
            <div class="mb-4">
                <label for="date" class="form-label">Data:</label>
                <input type="date" id="date" name="date" class="form-control" required>
            </div>

            <div class="row">
                <!-- Colonna dei pulsanti -->
                <div class="col-md-8">
                    <!-- Selezione meteo mattina -->
                    <div class="mb-4">
                        <label class="form-label">Descrizione Meteo - Mattina:</label>
                        <div class="weather-group">
                            <!-- Soleggiato -->
                            <button type="button" class="weather-btn" data-value="Soleggiato" data-bs-toggle="tooltip" title="Soleggiato" onclick="selectWeather(this, 'morning_desc')">☀️</button>
                            <!-- Parzialmente Nuvoloso -->
                            <button type="button" class="weather-btn" data-value="Parzialmente Nuvoloso" data-bs-toggle="tooltip" title="Parzialmente Nuvoloso" onclick="selectWeather(this, 'morning_desc')">⛅</button>
                            <!-- Nuvoloso -->
                            <button type="button" class="weather-btn" data-value="Nuvoloso" data-bs-toggle="tooltip" title="Nuvoloso" onclick="selectWeather(this, 'morning_desc')">☁️</button>
                            <!-- Pioggia -->
                            <button type="button" class="weather-btn" data-value="Pioggia" data-bs-toggle="tooltip" title="Pioggia" onclick="selectWeather(this, 'morning_desc')">🌧️</button>
                            <!-- Neve -->
                            <button type="button" class="weather-btn" data-value="Neve" data-bs-toggle="tooltip" title="Neve" onclick="selectWeather(this, 'morning_desc')">❄️</button>
                            <!-- Grandine -->
                            <button type="button" class="weather-btn" data-value="Grandine" data-bs-toggle="tooltip" title="Grandine" onclick="selectWeather(this, 'morning_desc')">⚽</button>
                            <!-- Temporale -->
                            <button type="button" class="weather-btn" data-value="Temporale" data-bs-toggle="tooltip" title="Temporale" onclick="selectWeather(this, 'morning_desc')">🌩️</button>
                        </div>
                        <input type="hidden" id="morning_desc" name="morning_desc" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Descrizione Meteo - Pomeriggio:</label>
                        <div class="weather-group">
                            <!-- Soleggiato -->
                            <button type="button" class="weather-btn" data-value="Soleggiato" data-bs-toggle="tooltip" title="Soleggiato" onclick="selectWeather(this, 'afternoon_desc')">☀️</button>
                            <!-- Parzialmente Nuvoloso -->
                            <button type="button" class="weather-btn" data-value="Parzialmente Nuvoloso" data-bs-toggle="tooltip" title="Parzialmente Nuvoloso" onclick="selectWeather(this, 'afternoon_desc')">⛅</button>
                            <!-- Nuvoloso -->
                            <button type="button" class="weather-btn" data-value="Nuvoloso" data-bs-toggle="tooltip" title="Nuvoloso" onclick="selectWeather(this, 'afternoon_desc')">☁️</button>
                            <!-- Pioggia -->
                            <button type="button" class="weather-btn" data-value="Pioggia" data-bs-toggle="tooltip" title="Pioggia" onclick="selectWeather(this, 'afternoon_desc')">🌧️</button>
                            <!-- Neve -->
                            <button type="button" class="weather-btn" data-value="Neve" data-bs-toggle="tooltip" title="Neve" onclick="selectWeather(this, 'afternoon_desc')">❄️</button>
                            <!-- Grandine -->
                            <button type="button" class="weather-btn" data-value="Grandine" data-bs-toggle="tooltip" title="Grandine" onclick="selectWeather(this, 'afternoon_desc')">⚽</button>
                            <!-- Temporale -->
                            <button type="button" class="weather-btn" data-value="Temporale" data-bs-toggle="tooltip" title="Temporale" onclick="selectWeather(this, 'afternoon_desc')">🌩️</button>
                        </div>
                        <input type="hidden" id="afternoon_desc" name="afternoon_desc" required>
                    </div>
                </div>

                <!-- Colonna della legenda -->
                <div class="col-md-4 mb-3">
                    <div class="card shadow-sm">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0">Legenda Meteo</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <li><span class="weather-icon">☀️</span> Soleggiato</li>
                                <li><span class="weather-icon">⛅</span> Parzialmente Nuvoloso</li>
                                <li><span class="weather-icon">☁️</span> Nuvoloso</li>
                                <li><span class="weather-icon">🌧️</span> Pioggia</li>
                                <li><span class="weather-icon">❄️</span> Neve</li>
                                <li><span class="weather-icon">⚽</span> Grandine (misto pioggia)</li>
                                <li><span class="weather-icon">🌩️</span> Temporale (misto pioggia)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>


            <div class="mb-4">
                <label for="temp_min" class="form-label text-primary">Temperatura Minima (°C):</label>
                <input type="number" id="temp_min" name="temp_min" step="0.1" class="form-control" value="0" required>
                <br/>
                <label for="temp_max" class="form-label text-danger">Temperatura Massima (°C):</label>
                <input type="number" id="temp_max" name="temp_max" step="0.1" class="form-control" value="0" required>
            </div>

            <!-- Campo nota facoltativa -->
            <div class="mb-3">
                <label for="note" class="form-label">Nota (facoltativa):</label>
                <textarea class="form-control" id="note" name="note" rows="3" placeholder="Inserisci un'allerta meteo o una nota rilevante..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Salva Previsione</button>
        </form>
    </div>
    <script>
        setTimeout(function() {
            let alertBox = document.getElementById('messageAlert');
            if (alertBox) {
                let alertInstance = new bootstrap.Alert(alertBox);
                alertInstance.close();
            }
        }, 3000);

        // Funzione per selezionare il meteo
        function selectWeather(button, inputId) {
            // Rimuovi lo stato "active" dagli altri pulsanti
            const buttons = button.parentElement.querySelectorAll('.weather-btn');
            buttons.forEach(btn => btn.classList.remove('active'));

            // Aggiungi lo stato "active" al pulsante selezionato
            button.classList.add('active');

            // Imposta il valore corrispondente nell'input nascosto
            document.getElementById(inputId).value = button.getAttribute('data-value');
        }

         // Verifica se i campi nascosti sono riempiti prima di inviare il form
         document.getElementById('forecastForm').addEventListener('submit', function (event) {
            const morningDesc = document.getElementById('morning_desc').value;
            const afternoonDesc = document.getElementById('afternoon_desc').value;

            if (!morningDesc || !afternoonDesc) {
                event.preventDefault(); // Impedisce l'invio del form
                alert('Per favore, seleziona una descrizione per il mattino e il pomeriggio.');
            }
        });

        document.addEventListener("DOMContentLoaded", function () {
            // Ottieni i parametri dall'URL
            const urlParams = new URLSearchParams(window.location.search);
            const dateParam = urlParams.get("date"); // Prendi il valore di "date" dall'URL

            if (dateParam) {
                document.querySelector('input[type="date"]').value = dateParam;
            }
        });
    </script>
    <script src="./assets/js/main.js"></script>
</body>
</html>
