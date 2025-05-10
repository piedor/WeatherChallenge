<?php
    // Include il file per il controllo della sessione
    include 'utils/check_session.php';

    // Messaggio di conferma o errore
    $message = $_GET['message'] ?? null;
    $type = $_GET['type'] ?? null; // "success" o "error"

    // Recupero delle previsioni esistenti per l'utente
    $query = "SELECT id, date, temp_max, temp_min, morning_desc, afternoon_desc, accuracy, temp_error, note FROM forecasts WHERE user_id = ? ORDER BY date DESC";
    $stmt = $__con->prepare($query);

    if ($stmt) {
        $stmt->bind_param("i", $user_id); // Assumi che $user_id sia definito come ID utente
        $stmt->execute();
        $result = $stmt->get_result(); // Ottieni i risultati
        $forecasts = [];
        
        while ($row = $result->fetch_assoc()) {
            $forecasts[] = $row; // Aggiungi ogni riga all'array $forecasts
        }
        
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
        <!-- Tabella con previsioni precedenti -->
        <div class="card shadow-sm mt-4">
            <div class="card-header text-white bg-secondary">
                <h4 class="mb-0">Le tue previsioni precedenti</h4>
            </div>
            <div class="card-body table-responsive">
                <?php if (!empty($forecasts)): ?>
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
                            <?php 
                            foreach ($forecasts as $forecast): 
                                $forecast_date = new DateTime($forecast['date']);
                                $today = new DateTime();
                                $today->setTime(0, 0, 0); // Rimuove l'ora per confronti solo sulla data    
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars(date("d/m/Y", strtotime($forecast['date']))) ?></td>
                                    <td><?= htmlspecialchars($forecast['temp_max']) ?></td>
                                    <td><?= htmlspecialchars($forecast['temp_min']) ?></td>
                                    <td><?= $weatherDescToEmoji[$forecast['morning_desc']] ?? htmlspecialchars($forecast['morning_desc']) ?></td>
                                    <td><?= $weatherDescToEmoji[$forecast['afternoon_desc']] ?? htmlspecialchars($forecast['afternoon_desc']) ?></td>
                                    <td><?= $forecast_date < $today ? htmlspecialchars($forecast['accuracy']) . '%' : 'Non disponibile' ?></td>
                                    <td><?= $forecast_date < $today ? '|' . htmlspecialchars(round($forecast['temp_error'], 1)) . '|°' : 'Non disponibile' ?></td>
                                    <td><?= htmlspecialchars($forecast['note']) ?></td>
                                    <td>
                                        <?php
                                            // Mostra il pulsante "Modifica" solo se la data è >= di oggi
                                            if ($forecast_date > $today) {
                                                echo '<a href="modify_forecast.php?id=' . htmlspecialchars($forecast['id']) . '" class="btn btn-primary btn-sm me-1">Modifica</a>';
                                                echo '<a href="delete_forecast.php?id=' . htmlspecialchars($forecast['id']) . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Sei sicuro di voler eliminare questa previsione?\');">Elimina</a>';
                                            } else {
                                                echo 'Non modificabile';
                                            }
                                        ?>
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
                <?php else: ?>
                    <p class="text-muted">Non hai ancora caricato alcuna previsione.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="./assets/js/main.js"></script>
</body>
</html>
