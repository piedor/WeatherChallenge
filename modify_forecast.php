<?php
    include 'utils/check_id_forecast.php';

    $message = $_GET['message'] ?? null;
    $type = "error";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $forecast_id    = $_POST['id'];
        $temp_min       = $_POST['temp_min'];
        $temp_max       = $_POST['temp_max'];
        $morning_desc   = $_POST['morning_desc'];
        $afternoon_desc = $_POST['afternoon_desc'];
        $note           = trim($_POST['note']);

        function isForecastSuspicious($conn, $forecast) {
            $sql = "SELECT * FROM weather_sources_forecasts WHERE date = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $forecast['date']);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $similar = 0;
                if (abs($row['temp_max'] - $forecast['temp_max']) <= 1) $similar++;
                if (abs($row['temp_min'] - $forecast['temp_min']) <= 1) $similar++;
                if (strcasecmp($row['morning_desc'],   $forecast['morning_desc'])   == 0) $similar++;
                if (strcasecmp($row['afternoon_desc'], $forecast['afternoon_desc']) == 0) $similar++;
                if ($similar >= 3) return true;
            }
            return false;
        }

        $forecast = [
            'date'          => $forecast['date'],
            'temp_min'      => $temp_min,
            'temp_max'      => $temp_max,
            'morning_desc'  => $morning_desc,
            'afternoon_desc'=> $afternoon_desc
        ];

        $suspiciousFlag = isForecastSuspicious($__con, $forecast) ? 1 : 0;

        $query = "UPDATE forecasts SET temp_min = ?, temp_max = ?, morning_desc = ?, afternoon_desc = ?, note = ?, updated_at = NOW(), is_sosp = ? WHERE id = ?";
        $stmt  = $__con->prepare($query);
        $stmt->bind_param("ssssssi", $temp_min, $temp_max, $morning_desc, $afternoon_desc, $note, $suspiciousFlag, $forecast_id);

        if ($stmt->execute()) {
            $message = "Previsione aggiornata con successo!";
            $type    = "success";
        } else {
            $message = "Errore durante l'aggiornamento della previsione: " . $stmt->error;
        }
        header("Location: history_forecast.php?message=" . urlencode($message) . "&type=" . urlencode($type));
        exit;
    }

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
    <link rel="stylesheet" href="./assets/css/style_app.css?v=<?php echo filemtime('assets/css/style_app.css'); ?>">
    <link rel="stylesheet" href="./assets/css/style_insert_forecast.css?v=<?php echo filemtime('assets/css/style_insert_forecast.css'); ?>">
</head>
<body class="bg-light">
    <?php require('./utils/header.php'); ?>

    <div class="container mt-2">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Modifica Previsione del <?= $forecast_date->format('d/m/Y') ?></h4>
            </div>
            <div class="card-body">

                <?php if ($message): ?>
                    <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>

                <form method="POST" action="" id="modifyForm">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">

                    <div class="row g-3">

                        <!-- Colonna bottoni -->
                        <div class="col-md-8">

                            <!-- Mattina -->
                            <div class="mb-4">
                                <label class="form-label">Meteo Mattina</label>
                                <input type="hidden" id="morning_desc" name="morning_desc" value="<?= htmlspecialchars($forecast['morning_desc']) ?>">
                                <div class="weather-group">
                                    <?php foreach ($weatherDescToEmoji as $desc => $icon): ?>
                                        <button type="button"
                                            class="weather-btn <?= $forecast['morning_desc'] === $desc ? 'selected' : '' ?>"
                                            data-target="morning_desc"
                                            data-bs-toggle="tooltip" title="<?= $desc ?>"
                                            data-value="<?= $desc ?>">
                                            <?= $icon ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Pomeriggio -->
                            <div class="mb-4">
                                <label class="form-label">Meteo Pomeriggio</label>
                                <input type="hidden" id="afternoon_desc" name="afternoon_desc" value="<?= htmlspecialchars($forecast['afternoon_desc']) ?>">
                                <div class="weather-group">
                                    <?php foreach ($weatherDescToEmoji as $desc => $icon): ?>
                                        <button type="button"
                                            class="weather-btn <?= $forecast['afternoon_desc'] === $desc ? 'selected' : '' ?>"
                                            data-target="afternoon_desc"
                                            data-bs-toggle="tooltip" title="<?= $desc ?>"
                                            data-value="<?= $desc ?>">
                                            <?= $icon ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
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
                            <label for="temp_min" class="form-label text-primary">
                                <span class="d-md-none">Temperatura Min (°C):</span>
                                <span class="d-none d-md-inline">Temperatura Minima (°C):</span>
                            </label>
                            <input type="number" id="temp_min" name="temp_min" step="0.1" class="form-control"
                                value="<?= htmlspecialchars($forecast['temp_min']) ?>" required>
                        </div>
                        <div>
                            <label for="temp_max" class="form-label text-danger">
                                <span class="d-md-none">Temperatura Max (°C):</span>
                                <span class="d-none d-md-inline">Temperatura Massima (°C):</span>
                            </label>
                            <input type="number" id="temp_max" name="temp_max" step="0.1" class="form-control"
                                value="<?= htmlspecialchars($forecast['temp_max']) ?>" required>
                        </div>
                    </div>

                    <!-- Nota -->
                    <div class="mb-3">
                        <label for="note" class="form-label">Nota (facoltativa):</label>
                        <textarea class="form-control" id="note" name="note" rows="3"><?= htmlspecialchars($forecast['note']) ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-success w-100 mb-3">Salva Modifiche</button>
                </form>

                <a href="index.php" class="btn btn-secondary w-100">Torna alla Dashboard</a>

            </div>
        </div>
    </div>

    <script>
        // Selezione bottoni meteo
        document.querySelectorAll('.weather-btn').forEach(button => {
            button.addEventListener('click', function() {
                const target = this.dataset.target;
                document.querySelectorAll(`.weather-btn[data-target="${target}"]`)
                        .forEach(btn => btn.classList.remove('selected'));
                document.getElementById(target).value = this.dataset.value;
                this.classList.add('selected');
            });
        });

        // Toggle legenda
        document.getElementById('legendToggle').addEventListener('click', function() {
            this.classList.toggle('open');
            document.getElementById('legendCard').classList.toggle('visible');
        });

        // Validazione temperature
        document.getElementById('modifyForm').addEventListener('submit', function(e) {
            const tempMin = parseFloat(document.getElementById('temp_min').value);
            const tempMax = parseFloat(document.getElementById('temp_max').value);
            if (tempMin > tempMax) {
                e.preventDefault();
                alert('Errore: La temperatura minima non può essere maggiore della massima.');
            }
        });
    </script>
    <script src="./assets/js/main.js?v=2"></script>
</body>
</html>