<?php
    include 'utils/check_session.php';

    $message = $_GET['message'] ?? null;
    $type    = $_GET['type'] ?? null;

    $query = "SELECT id, date, temp_max, temp_min, morning_desc, afternoon_desc, accuracy, temp_error, note FROM forecasts WHERE user_id = ? ORDER BY date DESC";
    $stmt  = $__con->prepare($query);

    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result    = $stmt->get_result();
        $forecasts = [];
        while ($row = $result->fetch_assoc()) $forecasts[] = $row;
        $stmt->close();
    } else {
        echo "Errore nella preparazione della query: " . $__con->error;
    }
?>
<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Storico Previsioni</title>
        <meta name="description" content="WebApp previsioni meteo">
        <meta name="author" content="Pietro Dorighi">
        <link href="./favicon.ico" rel="shortcut icon" type="image/vnd.microsoft.icon">
        <?php require_once './utils/style.php'; ?>
        <link rel="stylesheet" href="./assets/css/style_app.css?v=<?php echo filemtime('assets/css/style_app.css'); ?>">
        <link rel="stylesheet" href="./assets/css/style_history_forecast.css?v=<?php echo filemtime('assets/css/style_history_forecast.css'); ?>">
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
            <div class="card shadow-sm">
                <div class="card-header text-white bg-secondary">
                    <h4 class="mb-0">Le tue previsioni precedenti</h4>
                </div>
                <div class="card-body">

                    <?php if (!empty($forecasts)): ?>

                        <!-- ══════ DESKTOP: tabella invariata ══════ -->
                        <div class="forecast-table table-responsive">
                            <table class="table table-striped text-center">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th class="text-danger">Temperatura Max (°C)</th>
                                        <th class="text-primary">Temperatura Min (°C)</th>
                                        <th>Mattina</th>
                                        <th>Pomeriggio</th>
                                        <th>Accuratezza totale</th>
                                        <th>Errore medio temperatura</th>
                                        <th>Note</th>
                                        <th>Azioni</th>
                                        <th>Dettagli</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($forecasts as $forecast):
                                        $forecast_date = new DateTime($forecast['date']);
                                        $today = new DateTime(); $today->setTime(0,0,0); ?>
                                        <tr>
                                            <td><?= htmlspecialchars(date("d/m/Y", strtotime($forecast['date']))) ?></td>
                                            <td><?= htmlspecialchars($forecast['temp_max']) ?></td>
                                            <td><?= htmlspecialchars($forecast['temp_min']) ?></td>
                                            <td><?= $weatherDescToEmoji[$forecast['morning_desc']]   ?? htmlspecialchars($forecast['morning_desc']) ?></td>
                                            <td><?= $weatherDescToEmoji[$forecast['afternoon_desc']] ?? htmlspecialchars($forecast['afternoon_desc']) ?></td>
                                            <td><?= $forecast_date < $today ? htmlspecialchars($forecast['accuracy']) . '%' : 'Non disponibile' ?></td>
                                            <td><?= $forecast_date < $today ? '|' . htmlspecialchars(round($forecast['temp_error'], 1)) . '|°' : 'Non disponibile' ?></td>
                                            <td><?= htmlspecialchars($forecast['note']) ?></td>
                                            <td>
                                                <?php if ($forecast_date > $today): ?>
                                                    <a href="modify_forecast.php?id=<?= htmlspecialchars($forecast['id']) ?>" class="btn btn-primary btn-sm me-1">Modifica</a>
                                                    <a href="delete_forecast.php?id=<?= htmlspecialchars($forecast['id']) ?>" class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Sei sicuro di voler eliminare questa previsione?');">Elimina</a>
                                                <?php else: ?>
                                                    Non modificabile
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($forecast_date < $today): ?>
                                                    <a href="details_forecast.php?id=<?= $forecast['id'] ?>" class="btn btn-info btn-sm">Dettagli</a>
                                                <?php else: ?>
                                                    Non disponibile
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- ══════ MOBILE: card ══════ -->
                        <div class="forecast-cards">
                            <?php foreach ($forecasts as $forecast):
                                $forecast_date = new DateTime($forecast['date']);
                                $today = new DateTime(); $today->setTime(0,0,0);
                                $isPast   = $forecast_date < $today;
                                $isFuture = $forecast_date > $today;

                                // Badge accuratezza
                                $acc = $forecast['accuracy'];
                                if (!$isPast) {
                                    $badgeClass = 'gray'; $badgeText = 'In attesa';
                                } elseif ($acc >= 60) {
                                    $badgeClass = 'green'; $badgeText = $acc . '%';
                                } else {
                                    $badgeClass = 'red';   $badgeText = $acc . '%';
                                }
                            ?>
                            <div class="f-card">

                                <!-- Data + badge -->
                                <div class="f-card-top">
                                    <span class="f-card-date">
                                        <?= htmlspecialchars(date("d/m/Y", strtotime($forecast['date']))) ?>
                                    </span>
                                    <span class="f-acc-badge <?= $badgeClass ?>"><?= $badgeText ?></span>
                                </div>

                                <!-- Griglia info -->
                                <div class="f-card-grid">
                                    <div class="f-card-item">
                                        <label>Mattina</label>
                                        <span><?= $weatherDescToEmoji[$forecast['morning_desc']] ?? htmlspecialchars($forecast['morning_desc']) ?></span>
                                    </div>
                                    <div class="f-card-item">
                                        <label>Pomeriggio</label>
                                        <span><?= $weatherDescToEmoji[$forecast['afternoon_desc']] ?? htmlspecialchars($forecast['afternoon_desc']) ?></span>
                                    </div>
                                    <div class="f-card-item">
                                        <label>Temp. Max</label>
                                        <span class="temp-max"><?= htmlspecialchars($forecast['temp_max']) ?>°C</span>
                                    </div>
                                    <div class="f-card-item">
                                        <label>Temp. Min</label>
                                        <span class="temp-min"><?= htmlspecialchars($forecast['temp_min']) ?>°C</span>
                                    </div>
                                    <?php if ($isPast): ?>
                                    <div class="f-card-item">
                                        <label>Errore temp.</label>
                                        <span>|<?= htmlspecialchars(round($forecast['temp_error'], 1)) ?>|°</span>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Nota -->
                                <?php if (!empty($forecast['note'])): ?>
                                    <div class="f-card-note">📝 <?= htmlspecialchars($forecast['note']) ?></div>
                                <?php endif; ?>

                                <!-- Azioni -->
                                <div class="f-card-actions">
                                    <?php if ($isFuture): ?>
                                        <a href="modify_forecast.php?id=<?= htmlspecialchars($forecast['id']) ?>"
                                        class="btn btn-primary btn-sm">✏️ Modifica</a>
                                        <a href="delete_forecast.php?id=<?= htmlspecialchars($forecast['id']) ?>"
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('Sei sicuro di voler eliminare questa previsione?');">🗑️ Elimina</a>
                                    <?php elseif ($isPast): ?>
                                        <a href="details_forecast.php?id=<?= $forecast['id'] ?>"
                                        class="btn btn-info btn-sm">🔍 Dettagli</a>
                                    <?php endif; ?>
                                </div>

                            </div>
                            <?php endforeach; ?>
                        </div>

                    <?php else: ?>
                        <p class="text-muted">Non hai ancora caricato alcuna previsione.</p>
                    <?php endif; ?>

                </div>
            </div>
        </div>

        <script>
            setTimeout(function() {
                const alertBox = document.getElementById('messageAlert');
                if (alertBox) new bootstrap.Alert(alertBox).close();
            }, 3000);
        </script>
        <script src="./assets/js/main.js?v=2"></script>
    </body>
</html>