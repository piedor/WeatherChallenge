<?php
    // Include il file per il controllo della sessione
    include 'utils/check_session.php';

    $id = $_GET['id'] ?? null;
    if (!$id) {
        redirectToErrorPage(0, "ID previsione non valido.");
    }

    // Recupera la previsione dell'utente
    $query = "SELECT * FROM weather_sources_forecasts WHERE id = ?";
    $stmt = $__con->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $forecast = $result->fetch_assoc();
    $stmt->close();

    $date = $forecast['date'];

    // Recupera il nome utente per la previsione
    $query = "SELECT name FROM weather_sources WHERE id = ?";
    $stmt = $__con->prepare($query);
    $stmt->bind_param("i", $forecast['weather_source_id']); // "i" perché l'id è intero
    $stmt->execute();
    $result = $stmt->get_result();
    $fullNameForecaster = $result->fetch_assoc()["name"];
    $stmt->close();

    // Recupera i dati meteo salvati in `weather_codes`
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

    // Decodifica i codici meteo salvati
    $weatherCodes = json_decode($weatherData['weather_codes'], true);

    // Array delle ore per il grafico
    $hoursSeries = [];
    for ($i = 0; $i < 24; $i++) {
        $hoursSeries[] = sprintf("%02d:00", $i);
    }

    $weatherDescriptionsSeries = [];
    $weatherIconsSeries = [];
    foreach ($weatherCodes as $code) {
        $weatherDescriptionsSeries[] = $wmoCodeToDescEmoji[$code][0] ?? "Sconosciuto";
        $weatherIconsSeries[] = $wmoCodeToDescEmoji[$code][1] ?? "❓";
    }

    // Passiamo i dati a JavaScript
    $weatherJson = json_encode($weatherDescriptionsSeries);
    $hoursSeriesJson = json_encode($hoursSeries);
    $weatherIconsSeriesJson = json_encode($weatherIconsSeries);

    // Chiamata API per la temperatura oraria di $date
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
    $apiUrl = "$baseUrl/StazioneMeteo/dashboard/api/get_temperatures_station.php?interval=hourly&date=" . $date;
    $response = file_get_contents($apiUrl);
    $temperatureData = json_decode($response); // Decodifica il JSON
    $temperatures = array_map(fn($t) => round($t, 1), $temperatureData->data[3]->values->avg ?? []); // Estrai le temperature
    $temperatureJson = json_encode($temperatures); // Converti per JS

    // Chiamata API per temperatura giornalieri di $date
    $apiUrl = "$baseUrl/StazioneMeteo/dashboard/api/get_temperatures_station.php?interval=daily&date=" . $date;
    $response = file_get_contents($apiUrl);
    $data = json_decode($response); // Decodifica il JSON
    $realTempAvg = round($data->data[3]->values->avg[0], 1); // Temperatura media
    $realTempMax = round($data->data[3]->values->max[0], 1); // Temperatura massima
    $realTempMin = round($data->data[3]->values->min[0], 1); // Temperatura minima

    // Chiamata API per calcolo accuratezza METEO in base ai codici meteo reali e alle previsioni dell'utente
    $apiUrl = "$baseUrl/StazioneMeteo/dashboard/api/calculate_weather_accuracy.php";

    $data = [
        "weather_codes" => $weatherCodes,
        "morning_desc" => $forecast['morning_desc'],
        "afternoon_desc" => $forecast['afternoon_desc']
    ];
    
    $options = [
        "http" => [
            "header"  => "Content-Type: application/json\r\n",
            "method"  => "POST",
            "content" => json_encode($data)
        ]
    ];
    
    $context  = stream_context_create($options);
    $response = file_get_contents($apiUrl, false, $context);
    
    if ($response === FALSE) {
        redirectToErrorPage(0, "Errore: errore nella richiesta API per il calcolo delle accuratezze meteo.");
    }
    
    $accuracyData = json_decode($response, true);
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
    <link rel="stylesheet" href="./assets/css/style_app.css">
    <link rel="stylesheet" href="./assets/css/style_dashboard.css">
</head>
<body class="bg-light">
    <?php require ('./utils/header.php'); ?>
    <div class="container mt-2">
        <div class="card shadow-sm">
            <div class="card-header bg-info text-white">
                <h4>Dettagli Previsione per <?= htmlspecialchars(date("d/m/Y", strtotime($forecast['date']))) ?></h4>
            </div>
            <div class="card-body text-center">
                <!-- Grafico -->
                <div id="weatherChart"></div>
                <p class=""><strong>Temperatura media giornaliera:</strong> <?= $realTempAvg ?>°C</p>
                <p class="text-danger"><strong>Temperatura massima giornaliera:</strong> <?= $realTempMax ?>°C</p>
                <p class="text-primary"><strong>Temperatura minima giornaliera:</strong> <?= $realTempMin ?>°C</p>
            </div>
        </div>
        <div class="container mt-5">
            <div class="card shadow-sm">
                <div class="card-body text-center">                    
                    <h2>Previsione di <?= $fullNameForecaster ?></h2>
                    <p class="text-danger"><strong>Temperatura massima prevista:</strong> <?= htmlspecialchars($forecast['temp_max']) ?>°C</p>
                    <p class="text-primary"><strong>Temperatura minima prevista:</strong> <?= htmlspecialchars($forecast['temp_min']) ?>°C</p>
                    <p><strong>Mattina:</strong> <?= htmlspecialchars($forecast['morning_desc']) ?> <?= $weatherDescToEmoji[$forecast['morning_desc']] ?></p>
                    <p><strong>Pomeriggio:</strong> <?= htmlspecialchars($forecast['afternoon_desc']) ?> <?= $weatherDescToEmoji[$forecast['afternoon_desc']] ?></p>

                    <h3 class="text-success">Accuratezza del <?= htmlspecialchars($forecast['accuracy']) ?>%</h3>
                    <p><strong>Accuratezza temperatura giornaliera:</strong> <?= htmlspecialchars($forecast['temp_accuracy']) ?>%</p>
                    <p><strong>Accuratezza condizioni meteorologiche:</strong> <?= htmlspecialchars($forecast['weather_accuracy']) ?>%</p>
                
                    <button class="btn btn-outline-primary mt-3" type="button" data-bs-toggle="collapse" data-bs-target="#dettagliCalcolo">
                        Mostra Dettagli Calcolo 🔍
                    </button>

                    <div class="collapse mt-3" id="dettagliCalcolo">
                        <div class="card card-body">
                            <h5>Calcolo Accuratezza 📊</h5>

                            <hr>
                            <p><b>Temperatura massima prevista:</b> <?= htmlspecialchars($forecast['temp_max']) ?>°C</p>
                            <p><b>Temperatura massima reale:</b> <?= $realTempMax ?>°C</p>
                            <p><b>Errore assoluto:</b> |<?= htmlspecialchars($forecast['temp_max']) ?> - <?= $realTempMax ?>| = <b><?= abs($forecast['temp_max']-$realTempMax) ?>°C</b></p>
                            <p><b>Temperatura minima prevista:</b> <?= htmlspecialchars($forecast['temp_min']) ?>°C</p>
                            <p><b>Temperatura minima reale:</b> <?= $realTempMin ?>°C</p>
                            <p><b>Errore assoluto:</b> |<?= htmlspecialchars($forecast['temp_min']) ?> - <?= $realTempMin ?>| = <b><?= abs($forecast['temp_min']-$realTempMin) ?>°C</b></p>
                            <p><b>Accuratezza temperatura:</b> <?= htmlspecialchars($forecast['temp_accuracy']) ?>%</p>

                            <hr>

                            <p><b>Condizioni meteo previste (Mattina):</b> <?= htmlspecialchars($forecast['morning_desc']) ?> <?= $weatherDescToEmoji[$forecast['morning_desc']] ?></p>
                            <p><b>Condizioni meteo reali (Mattina):</b> 
                                <?php foreach ($accuracyData["dominant_conditions"]["morning"] as $condition): ?>
                                    <?= htmlspecialchars($condition); ?> <?= $weatherDescToEmoji[$condition] ?? "oppure"; ?> &nbsp;
                                <?php endforeach; ?>
                            </p>
                            <p><b>Punteggio meteo Mattina:</b> <span class="text-success"><?= htmlspecialchars($accuracyData["accuracy"]["morning"]); ?>%</span></p>

                            <p><b>Condizioni meteo previste (Pomeriggio):</b> <?= htmlspecialchars($forecast['afternoon_desc']) ?> <?= $weatherDescToEmoji[$forecast['afternoon_desc']] ?></p>
                            <p><b>Condizioni meteo reali (Pomeriggio):</b>
                                <?php foreach ($accuracyData["dominant_conditions"]["afternoon"] as $condition): ?>
                                    <?= htmlspecialchars($condition); ?> <?= $weatherDescToEmoji[$condition] ?? "oppure"; ?> &nbsp;
                                <?php endforeach; ?>
                            </p>
                            <p><b>Punteggio meteo Pomeriggio:</b> <span class="text-success"><?= htmlspecialchars($accuracyData["accuracy"]["afternoon"]); ?>%</span></p>

                            <hr>
                            <p><b>Formula finale:</b></p>
                            <code>(<?= htmlspecialchars($forecast['temp_accuracy']) ?>% * 40% + ((<?= htmlspecialchars($accuracyData["accuracy"]["morning"]); ?>% + <?= htmlspecialchars($accuracyData["accuracy"]["afternoon"]); ?>%) / 2 ) * 60% ) = <?= round(($forecast['temp_accuracy']*0.4 + (($accuracyData["accuracy"]["morning"] + $accuracyData["accuracy"]["afternoon"]) / 2)*0.6), 2); ?>%</code>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <?php
            echo '<a href="weather_source_forecast_details.php?id=' . urlencode($forecast['weather_source_id']) . '" class="btn btn-secondary mt-3">← Torna alle previsioni del sito meteo</a>';
        ?>

    </div>

    <script>
        // Impostazioni grafici generale (lingua, ecc...)
        Highcharts.setOptions({
            global: {
                useUTC: false
            },
            lang: {
                contextButtonTitle: 'Menu',
                downloadCSV: 'Scarica CSV',
                downloadJPEG: 'Scarica JPEG',
                downloadMIDI: 'Scarica MIDI',
                downloadPDF: 'Scarica PDF',
                downloadPNG: 'Scarica PNG',
                downloadSVG: 'Scarica SVG',
                downloadXLS: 'Scarica XLS',
                exitFullscreen: 'Esci da schermo intero',
                hideData: 'Nascondi dati',
                loading: 'Caricamento...',
                months: ["Gennaio", "Febbraio", "Marzo", "Aprile", "Maggio", "Giugno", "Luglio", "Agosto", "Settembre", "Ottobre", "Novembre", "Dicembre"],
                noData: 'Nessun dato',
                playAsSound: 'Riproduci come suono',
                printChart: 'Stampa grafico', 
                shortMonths: ["Gen", "Feb", "Mar", "Apr", "Mag", "Giu", "Lug", "Ago", "Set", "Ott", "Nov", "Dic"],
                viewData: 'Visualizza dati',
                viewFullscreen: 'Visualizza a schermo intero',
                weekdays: ["Domenica", "Lunedì", "Martedì", "Mercoledì", "Giovedì", "Venerdì", "Sabato"]
            }
        });

        document.addEventListener("DOMContentLoaded", function () {
            const weatherIconsSeries = <?= $weatherIconsSeriesJson ?>;
            const hoursSeries = <?= $hoursSeriesJson ?>;
            const temperatures = <?= $temperatureJson ?>;

            Highcharts.chart("weatherChart", {
                chart: {
                    type: "spline"
                },
                title: {
                    text: "Condizioni Meteorologiche Reali ora per ora (dati di OpenMeteo) Trento"
                },
                xAxis: {
                    categories: hoursSeries, // Mantieni le ore come riferimento
                    title: {
                        text: "Ora del giorno"
                    },
                    labels: {
                        useHTML: true,
                        formatter: function () {
                            let hourIndex = parseInt(this.value.split(":")[0], 10); 
                            let icon = weatherIconsSeries[hourIndex] ?? "❓"; // Se manca, mostra "?"
                            
                            return `<div style="font-size: 24px; line-height: 30px;">
                                        ${icon}
                                    </div>
                                    <span style="font-size: 12px;">${this.value}</span>`;
                        },
                        style: {
                            fontSize: '14px'
                        }
                    }
                },
                yAxis: {
                    title: {
                        text: "Temperatura (°C)"
                    }
                },
                tooltip: {
                    shared: true,
                    useHTML: true,
                    formatter: function () {
                        let hourIndex = this.points[0].point.index;
                        return `<b>Ora: ${hoursSeries[hourIndex]}</b><br/>
                                Meteo: ${weatherIconsSeries[hourIndex]}<br/>
                                Temperatura: <b>${this.points[0].y}°C</b>`;
                    }
                },
                series: [
                    {
                        name: "Temperatura",
                        data: temperatures, // Passiamo i dati di temperatura
                        type: "spline", // Linea smussata
                        color: "#ff5733", // Arancione per evidenziare
                        marker: {
                            enabled: true
                        },
                        tooltip: {
                            valueSuffix: "°C"
                        }
                    }
                ],
                exporting: {
                    buttons: {
                        contextButton: {
                            menuItems: [
                                "viewFullscreen", 
                                "printChart", 
                                "separator", 
                                "downloadPNG", 
                                "downloadJPEG", 
                                "downloadPDF",
                                "downloadSVG",
                                "separator",
                                "downloadCSV",
                                "downloadXLS"
                            ]
                        }
                    }
                },
                credits: {
                    text: 'Liceo Da Vinci Trento',
                    href: 'https://liceodavincitn.it/'
                }
            });
        });
    </script>
    <script src="./assets/js/main.js"></script>
</body>
</html>
