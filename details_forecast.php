<?php
    // Include il file per il controllo della sessione e dei permessi
    include 'utils/check_id_forecast.php';

    $date = $forecast['date'];

    // Recupera il nome utente per la previsione
    $query = "SELECT full_name FROM users WHERE id = ?";
    $stmt = $__con->prepare($query);
    $stmt->bind_param("i", $forecast['user_id']); 
    $stmt->execute();
    $result = $stmt->get_result();
    $fullNameForecaster = $result->fetch_assoc()["full_name"];
    $stmt->close();

    // Recupera i dati meteo salvati
    $query = "SELECT weather_codes FROM weather_codes WHERE date = ?";
    $stmt = $__con->prepare($query);
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $weatherData = $result->fetch_assoc();
    $stmt->close();

    if (!$weatherData) {
        die("Dati meteo non trovati per questa data.");
    }

    $weatherCodes = json_decode($weatherData['weather_codes'], true);

    // Preparazione dati per il grafico
    $hoursSeries = [];
    for ($i = 0; $i < 24; $i++) {
        $hoursSeries[] = sprintf("%02d:00", $i);
    }

    $weatherIconsSeries = [];
    foreach ($weatherCodes as $code) {
        $weatherIconsSeries[] = $wmoCodeToDescEmoji[$code][1] ?? "❓";
    }

    // Logica Temperature (Stazione o OpenMeteo)
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
    $apiUrl = "$baseUrl/StazioneMeteo/dashboard/api/get_temperatures_station.php?interval=hourly&date=" . $date;
    $response = file_get_contents($apiUrl);
    $temperatureData = json_decode($response);
    
    if (!isset($temperatureData->message)) {
        $temperatures = array_map(fn($t) => round($t, 1), $temperatureData->data[3]->values->avg ?? []);
        $apiUrlDaily = "$baseUrl/StazioneMeteo/dashboard/api/get_temperatures_station.php?interval=daily&date=" . $date;
        $dataD = json_decode(file_get_contents($apiUrlDaily));
        $realTempAvg = round($dataD->data[3]->values->avg[0], 1);
        $realTempMax = round($dataD->data[3]->values->max[0], 1);
        $realTempMin = round($dataD->data[3]->values->min[0], 1);
    }

    if (!isset($temperatures) || count($temperatures) !== 24) {
        $apiUrlOM = "https://api.open-meteo.com/v1/forecast?latitude=46.0679&longitude=11.1211&hourly=temperature_2m&daily=temperature_2m_min,temperature_2m_max,temperature_2m_mean&start_date=$date&end_date=$date&timezone=Europe%2FRome";
        $dataOM = json_decode(file_get_contents($apiUrlOM));
        $temperatures = array_map(fn($t) => round($t, 1), $dataOM->hourly->temperature_2m ?? []);
        $realTempAvg = round($dataOM->daily->temperature_2m_mean[0], 1);
        $realTempMax = round($dataOM->daily->temperature_2m_max[0], 1);
        $realTempMin = round($dataOM->daily->temperature_2m_min[0], 1);
    }

    // Calcolo accuratezza tramite API
    $apiUrlAcc = "$baseUrl/StazioneMeteo/dashboard/api/calculate_weather_accuracy.php";
    $postData = ["weather_codes" => $weatherCodes, "morning_desc" => $forecast['morning_desc'], "afternoon_desc" => $forecast['afternoon_desc']];
    $options = ["http" => ["header" => "Content-Type: application/json\r\n", "method" => "POST", "content" => json_encode($postData)]];
    $accuracyData = json_decode(file_get_contents($apiUrlAcc, false, stream_context_create($options)), true);
?>

<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Dettagli Previsione</title>
        <meta name="description" content="WebApp previsioni meteo">
        <meta name="author" content="Pietro Dorighi">
        <link href="./favicon.ico" rel="shortcut icon" type="image/vnd.microsoft.icon">
        <script src="./assets/dist/js/highcharts/highcharts.js"></script>
        <?php require_once './utils/style.php'; ?>
        <link rel="stylesheet" href="./assets/css/style_app.css?v=<?php echo filemtime('assets/css/style_app.css'); ?>">
        <style>
            .card-stat { transition: transform 0.2s; border: none; border-top: 4px solid; }
            .card-max { border-top-color: #dc3545; }
            .card-min { border-top-color: #0d6efd; }
            .card-avg { border-top-color: #6c757d; }
            .table-modern thead { background-color: #f8f9fa; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; }
            .formula-box { background: #212529; color: #0dfd05; padding: 15px; border-radius: 8px; font-family: monospace; overflow-x: auto; }
            .option-badge { background: #e9ecef; padding: 2px 8px; border-radius: 12px; font-size: 0.85rem; margin-right: 4px; display: inline-block; }
        </style>
    </head>
    <body class="bg-light">
        <?php require ('./utils/header.php'); ?>
        
        <div class="container-fluid container-md">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0">Dati Reali - <?= date("d/m/Y", strtotime($forecast['date'])) ?></h4>
                </div>
                <div class="card-body">
                    <div id="weatherChart" class="mb-4" style="width:100%; height:380px;"></div>
                    
                    <div class="row g-3">
                        <div class="col-4">
                            <div class="card-stat card-avg bg-white p-3 shadow-sm text-center">
                                <small class="text-muted d-block">MEDIA</small>
                                <span class="h4 fw-bold"><?= $realTempAvg ?>°C</span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="card-stat card-max bg-white p-3 shadow-sm text-center">
                                <small class="text-danger d-block">MASSIMA</small>
                                <span class="h4 fw-bold text-danger"><?= $realTempMax ?>°C</span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="card-stat card-min bg-white p-3 shadow-sm text-center">
                                <small class="text-primary d-block">MINIMA</small>
                                <span class="h4 fw-bold text-primary"><?= $realTempMin ?>°C</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h3 class="fw-light">Previsione di <span class="fw-bold"><?= $fullNameForecaster ?></span></h3>
                        <div class="display-4 text-success fw-bold"><?= htmlspecialchars($forecast['accuracy']) ?>%</div>
                        <p class="text-muted">Punteggio Accuratezza Totale</p>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="p-3 border rounded bg-white text-center h-100">
                                <div class="text-uppercase text-muted small mb-2">Mattina</div>
                                <div class="h2 mb-1"><?= $weatherDescToEmoji[$forecast['morning_desc']] ?></div>
                                <div class="fw-bold"><?= htmlspecialchars($forecast['morning_desc']) ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded bg-white text-center h-100">
                                <div class="text-uppercase text-muted small mb-2">Pomeriggio</div>
                                <div class="h2 mb-1"><?= $weatherDescToEmoji[$forecast['afternoon_desc']] ?></div>
                                <div class="fw-bold"><?= htmlspecialchars($forecast['afternoon_desc']) ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 p-3 border rounded border-start-0 border-end-0 border-bottom-0">
                        <p class="mb-1"><strong>Note:</strong></p>
                        <p class="text-muted small italic"><?= $forecast['note'] === "" ? "Nessuna nota fornita." : htmlspecialchars($forecast['note']) ?></p>
                    </div>

                    <?php if ($forecast['is_plag']): ?>
                        <div class="alert alert-danger">⚠️ Segnalata per plagio.</div>
                    <?php endif; ?>

                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary btn-lg" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMath">
                            Mostra Analisi e Calcoli 🔍
                        </button>
                    </div>

                    <div class="collapse mt-4" id="collapseMath">
                        <div class="table-responsive">
                            <table class="table table-modern align-middle">
                                <thead>
                                    <tr>
                                        <th>Parametro Analizzato</th>
                                        <th>Tuo Dato</th>
                                        <th>Dato Reale / Opzioni Valide</th>
                                        <th class="text-center">Esito</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><i class="bi bi-thermometer-sun text-danger"></i> Temp. Massima</td>
                                        <td><?= $forecast['temp_max'] ?>°C</td>
                                        <td><?= $realTempMax ?>°C</td>
                                        <td rowspan="2" class="text-center border-start bg-light fw-bold">
                                            Accuratezza Termica:<br><span class="text-primary"><?= $forecast['temp_accuracy'] ?>%</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><i class="bi bi-thermometer-snow text-primary"></i> Temp. Minima</td>
                                        <td><?= $forecast['temp_min'] ?>°C</td>
                                        <td><?= $realTempMin ?>°C</td>
                                    </tr>
                                    <tr class="table-group-divider">
                                        <td><i class="bi bi-cloud-sun"></i> Condizioni Mattina</td>
                                        <td><?= $weatherDescToEmoji[$forecast['morning_desc']] ?> <small><?= $forecast['morning_desc'] ?></small></td>
                                        <td>
                                            <?php foreach ($accuracyData["dominant_conditions"]["morning"] as $cond): ?>
                                                <span class="option-badge"><?= $weatherDescToEmoji[$cond] ?? '' ?> <?= $cond ?></span>
                                            <?php endforeach; ?>
                                        </td>
                                        <td class="text-center border-start fw-bold"><?= $accuracyData["accuracy"]["morning"] ?>%</td>
                                    </tr>
                                    <tr>
                                        <td><i class="bi bi-sun"></i> Condizioni Pomeriggio</td>
                                        <td><?= $weatherDescToEmoji[$forecast['afternoon_desc']] ?> <small><?= $forecast['afternoon_desc'] ?></small></td>
                                        <td>
                                            <?php foreach ($accuracyData["dominant_conditions"]["afternoon"] as $cond): ?>
                                                <span class="option-badge"><?= $weatherDescToEmoji[$cond] ?? '' ?> <?= $cond ?></span>
                                            <?php endforeach; ?>
                                        </td>
                                        <td class="text-center border-start fw-bold"><?= $accuracyData["accuracy"]["afternoon"] ?>%</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            <h6 class="text-uppercase fw-bold text-muted small">Dettagli del Calcolo Matematico</h6>
                            <div class="p-3 border rounded bg-light mb-3">
                                <ul class="small mb-0">
                                    <li><b>Errore Max:</b> |<?= $forecast['temp_max'] ?> - <?= $realTempMax ?>| = <?= abs($forecast['temp_max'] - $realTempMax) ?>°C</li>
                                    <li><b>Errore Min:</b> |<?= $forecast['temp_min'] ?> - <?= $realTempMin ?>| = <?= abs($forecast['temp_min'] - $realTempMin) ?>°C</li>
                                    <li><b>Peso Temperature:</b> 40% del totale</li>
                                    <li><b>Peso Condizioni Meteo:</b> 60% del totale (30% Mattina + 30% Pomeriggio)</li>
                                </ul>
                            </div>
                            <div class="formula-box">
                                (<?= $forecast['temp_accuracy'] ?>% * 0.4) + ((<?= $accuracyData["accuracy"]["morning"] ?>% + <?= $accuracyData["accuracy"]["afternoon"] ?>%) / 2 * 0.6) = <?= $forecast['accuracy'] ?>%
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top">
                        <?php if (isset($role) && ($role === 'professor' || $role === 'admin') && $user_id !== $forecast['user_id']): ?>
                            <a href="report_plagiarism.php?id=<?= $forecast['id'] ?>" class="btn btn-danger mb-2 w-100">🚨 Segnala Plagio</a>
                        <?php endif; ?>
                        
                        <?php if ($user['full_name'] !== $fullNameForecaster): ?>
                            <a href="student_forecast_details.php?id=<?= urlencode($forecast['user_id']) ?>" class="btn btn-secondary w-100">← Torna alle previsioni studente</a>
                        <?php else: ?>
                            <a href="history_forecast.php" class="btn btn-secondary w-100">← Torna alla cronologia</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <script src="./assets/js/setup.js"></script> <script>
            document.addEventListener("DOMContentLoaded", function () {
                // 1. Applica le impostazioni lingua/globali

                // 2. Prepara i dati dal PHP
                const hours = <?= json_encode($hoursSeries) ?>;
                const icons = <?= json_encode($weatherIconsSeries) ?>;
                const temps = <?= json_encode($temperatures) ?>;

                createWeatherDetailsChart("weatherChart", hours, icons, temps);
            });
        </script>
        <script src="./assets/js/main.js?v=2"></script>
    </body>
</html>