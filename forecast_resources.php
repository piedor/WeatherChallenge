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
        <h2 class="text-center">🛠️ Strumenti per le Previsioni Meteo</h2>
        <p class="text-center">Qui puoi accedere rapidamente ai migliori strumenti per analizzare il meteo.</p>

        <div class="accordion" id="weatherTools">
            <!-- Spaghetti Meteo -->
            <div class="card">
                <div class="card-header" id="headingOne">
                    <h5 class="mb-0">
                        <button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                            🍝 Spaghetti Meteo (per Trento)
                        </button>
                    </h5>
                </div>
                <div id="collapseOne" class="collapse text-center" data-bs-parent="#weatherTools">
                    <div class="card-body text-center">
                        <a href="https://www.wetterzentrale.de/ens_image.php?model=gfs&member=ENS&geoid=70934&bw=1&var=201" target="_blank">
                            📊 Visualizza gli Spaghetti Meteo
                        </a>
                        <br/><br/>
                        <div class="container-fluid d-flex justify-content-center">
                            <div class="ratio ratio-16x9" style="width: 60vw; height: 100vh;">
                                <iframe 
                                    src="https://www.wetterzentrale.de/ens_image.php?model=gfs&member=ENS&geoid=70934&bw=1&var=201"
                                    title="Spaghetti Meteo"
                                    allowfullscreen
                                    style="border: none;">
                                </iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Mappe -->
            <div class="card">
                <div class="card-header" id="headingTwo">
                    <h5 class="mb-0">
                        <button class="btn btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                            📍 Mappe e Modelli Meteo
                        </button>
                    </h5>
                </div>
                <div id="collapseTwo" class="collapse text-center" data-bs-parent="#weatherTools">
                    <div class="card-body">
                        <a href="https://www.wetterzentrale.de/de/topkarten.php?model=gfs&lid=OP" target="_blank">🌍 Wetterzentrale - Modelli GFS</a>
                        <br/><br/>
                        <div class="container-fluid d-flex justify-content-center">
                            <div class="ratio ratio-16x9" style="width: 60vw; height: 100vh;">
                                <iframe 
                                    src="https://www.wetterzentrale.de/de/topkarten.php?model=gfs&lid=OP"
                                    title="Spaghetti Meteo"
                                    allowfullscreen
                                    style="border: none;">
                                </iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Radar Meteo -->
            <div class="card">
                <div class="card-header" id="headingThree">
                    <h5 class="mb-0">
                        <button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                            📡 Radar e Dati Meteo in Tempo Reale
                        </button>
                    </h5>
                </div>
                <div id="collapseThree" class="collapse text-center" data-bs-parent="#weatherTools">
                    <div class="card-body">
                        <a href="https://www.meteonetwork.it/rete/livemap/" target="_blank">🌍 MeteoNetwork - Live Map</a><br>
                        <br/><br/>
                        <div class="container-fluid d-flex justify-content-center">
                            <div class="ratio ratio-16x9" style="width: 60vw; height: 100vh;">
                                <iframe 
                                    src="https://www.meteonetwork.it/rete/livemap/"
                                    title="Spaghetti Meteo"
                                    allowfullscreen
                                    style="border: none;">
                                </iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Previsioni Temporali -->
            <div class="card">
                <div class="card-header" id="headingFour">
                    <h5 class="mb-0">
                        <button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour">
                            ⛈️ Previsioni Temporali
                        </button>
                    </h5>
                </div>
                <div id="collapseFour" class="collapse text-center" data-bs-parent="#weatherTools">
                    <div class="card-body">
                        <a href="http://www.estofex.org/" target="_blank">⚡ Estofex - Previsione Temporali</a>
                    </div>
                </div>
            </div>

            <!-- Vento e Meteo a Breve Termine -->
            <div class="card">
                <div class="card-header" id="headingFive">
                    <h5 class="mb-0">
                        <button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive">
                            🌬️ Vento e Meteo a Breve Termine
                        </button>
                    </h5>
                </div>
                <div id="collapseFive" class="collapse text-center" data-bs-parent="#weatherTools">
                    <div class="card-body">
                        <a href="https://www.windy.com/" target="_blank">🌪️ Windy - Previsioni Vento</a><br>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="./assets/js/main.js"></script>
</body>
</html>
