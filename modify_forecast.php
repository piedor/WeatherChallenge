<?php
    // Include il file per il controllo della sessione e che la previsione appartenga all'utente corrente
    include 'utils/check_id_forecast.php';

    // Messaggio di conferma o errore
    $message = $_GET['message'] ?? null;
    $type = "error";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $forecast_id = $_POST['id'];
        $temp_min = $_POST['temp_min'];
        $temp_max = $_POST['temp_max'];
        $morning_desc = $_POST['morning_desc'];
        $afternoon_desc = $_POST['afternoon_desc'];
        $note = trim($_POST['note']);

        // Aggiorna la previsione nel database
        $query = "UPDATE forecasts SET temp_min = ?, temp_max = ?, morning_desc = ?, afternoon_desc = ?, note = ?, updated_at=now() WHERE id = ?";
        $stmt = $__con->prepare($query);
        $stmt->bind_param("sssssi", $temp_min, $temp_max, $morning_desc, $afternoon_desc, $note, $forecast_id);


        if ($stmt->execute()) {
            $message = "Previsione aggiornata con successo!";
            $type = "success";
        } else {
            $message = "Errore durante l'aggiornamento della previsione: " . $stmt->error;
        }
        header("Location: history_forecast.php?message=" . urlencode($message) . "&type=" . urlencode($type));
        exit;
    }

    // Controlla se la data della previsione è modificabile
    $forecast_date = new DateTime($forecast['date']);
    $today = new DateTime();
    $today->setTime(0, 0, 0);
    if ($forecast_date <= $today) {
        redirectToErrorPage(0, "Non è possibile modificare previsioni per date passate.");
    }
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifica Previsione</title>
    <meta name="description" content="WebApp previsioni meteo">
    <meta name="author" content="Pietro Dorighi">
    <link href="./favicon.ico" rel="shortcut icon" type="image/vnd.microsoft.icon">
    <?php require_once './utils/style.php'; ?>
    <link rel="stylesheet" href="./assets/css/style_app.css">
    <link rel="stylesheet" href="./assets/css/style_dashboard.css">
</head>
<body class="bg-light">
    <?php require ('./utils/header.php'); ?>
    <div class="container mt-2">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4>Modifica Previsione del <?= $forecast_date->format('d/m/Y') ?></h4>
            </div>
            <div class="card-body">
                <?php if ($message): ?>
                    <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">

                    <div class="row">
                        <!-- Colonna dei pulsanti -->
                        <div class="col-md-8">
                            <!-- Selezione meteo mattutino -->
                            <div class="mb-3">
                                <label class="form-label">Meteo Mattina</label>
                                <input type="hidden" id="morning_desc" name="morning_desc" value="<?= htmlspecialchars($forecast['morning_desc']) ?>">
                                <div class="d-flex flex-wrap">
                                    <div class="weather-group">
                                        <?php foreach ($weatherDescToEmoji as $desc => $icon): ?>
                                            <button type="button" class="weather-btn <?= $forecast['morning_desc'] === $desc ? 'selected' : '' ?>" 
                                                    data-target="morning_desc" data-bs-toggle="tooltip" title="<?= $desc ?>" data-value="<?= $desc ?>">
                                                <?= $icon ?>
                                            </button>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Selezione meteo pomeridiano -->
                            <div class="mb-3">
                                <label class="form-label">Meteo Pomeriggio</label>
                                <input type="hidden" id="afternoon_desc" name="afternoon_desc" value="<?= htmlspecialchars($forecast['afternoon_desc']) ?>">
                                <div class="d-flex flex-wrap">
                                    <div class="weather-group">
                                        <?php foreach ($weatherDescToEmoji as $desc => $icon): ?>
                                            <button type="button" class="weather-btn <?= $forecast['afternoon_desc'] === $desc ? 'selected' : '' ?>" 
                                                    data-target="afternoon_desc" data-bs-toggle="tooltip" title="<?= $desc ?>" data-value="<?= $desc ?>">
                                                <?= $icon ?>
                                            </button>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
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
                                        <li><span class="weather-icon">☔</span> Pioggia</li>
                                        <li><span class="weather-icon">❄️</span> Neve</li>
                                        <li><span class="weather-icon">⚽</span> Grandine (misto pioggia)</li>
                                        <li><span class="weather-icon">🌩️</span> Temporale (misto pioggia)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Temperatura -->
                    <div class="mb-3">
                        <label for="temp_min" class="form-label text-primary">Temperatura Minima (°C):</label>
                        <input type="number" id="temp_min" name="temp_min" step="0.1" class="form-control" value="<?= htmlspecialchars($forecast['temp_min']) ?>" required>
                        <br/>
                        <label for="temp_max" class="form-label text-danger">Temperatura Massima (°C):</label>
                        <input type="number" id="temp_max" name="temp_max" step="0.1" class="form-control" value="<?= htmlspecialchars($forecast['temp_max']) ?>" required>
                    </div>

                    <!-- Campo nota facoltativa -->
                    <div class="mb-3">
                        <label for="note" class="form-label">Nota (facoltativa):</label>
                        <textarea class="form-control" id="note" name="note" rows="3"><?= htmlspecialchars($forecast['note']) ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-success">Salva Modifiche</button>
                </form>
                <hr>
                <a href="index.php" class="btn btn-secondary mt-3">Torna alla Dashboard</a>
            </div>
        </div>
    </div>

    <script>
        // Selezione delle icone meteo
        document.querySelectorAll('.weather-btn').forEach(button => {
            button.addEventListener('click', function() {
                let targetField = document.getElementById(this.dataset.target);
                
                // Rimuove la selezione dagli altri pulsanti dello stesso gruppo
                document.querySelectorAll(`.weather-btn[data-target="${this.dataset.target}"]`).forEach(btn => {
                    btn.classList.remove('selected');
                });

                // Imposta il valore selezionato e evidenzia il pulsante selezionato
                targetField.value = this.dataset.value;
                this.classList.add('selected');
            });
        });
        document.querySelector("form").addEventListener("submit", function(event) {
            let tempMin = parseFloat(document.getElementById("temp_min").value);
            let tempMax = parseFloat(document.getElementById("temp_max").value);

            if (tempMin > tempMax) {
                event.preventDefault(); // Blocca l'invio del form
                alert("Errore: La temperatura minima non può essere maggiore della massima.");
            }
        });
    </script>
    <script src="./assets/js/main.js"></script>
</body>
</html>
