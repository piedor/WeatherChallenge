<?php
    // Include il file per il controllo della sessione e che la previsione appartenga all'utente corrente oppure di uno studente e l'account sia di un professore
    include 'utils/check_id_forecast.php';

    // Solo i professori e admin possono accedere
    if ($role !== 'professor' && $role !== 'admin') {
        header('Location: index.php');
        exit;
    }

    // Messaggio di conferma o errore
    $message = $_GET['message'] ?? null;
    $type = $_GET['type'] ?? null; // "success" o "error"

    // Evita che il professore segnali una propria previsione
    if ($forecast['user_id'] == $user_id) {
        redirectToErrorPage(0, "Non puoi segnalare una tua previsione.");
        exit;
    }

    // Recupera il nome utente per la previsione
    $query = "SELECT full_name FROM users WHERE id = ?";
    $stmt = $__con->prepare($query);
    $stmt->bind_param("i", $forecast['user_id']); // "i" perché l'id è intero
    $stmt->execute();
    $result = $stmt->get_result();
    $fullNameForecaster = $result->fetch_assoc()["full_name"];
    $stmt->close();
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
    
    <div class="container mt-4">
        <div class="card shadow-sm">
            <div class="card-header">
                <h2>Segnalazione Previsione Sospetta</h2>
                <p>Stai segnalando una previsione inserita da <strong><?= $fullNameForecaster ?></strong>.</p>
            </div>
        
            <div class="card-body">
                <p><strong>Data:</strong> <?= htmlspecialchars(date("d/m/Y", strtotime($forecast['date']))) ?></p>
                <p class="text-danger"><strong>Temperatura massima prevista:</strong> <?= htmlspecialchars($forecast['temp_max']) ?>°C</p>
                <p class="text-primary"><strong>Temperatura minima prevista:</strong> <?= htmlspecialchars($forecast['temp_min']) ?>°C</p>
                <p><strong>Mattina:</strong> <?= htmlspecialchars($forecast['morning_desc']) ?> <?= $weatherDescToEmoji[$forecast['morning_desc']] ?></p>
                <p><strong>Pomeriggio:</strong> <?= htmlspecialchars($forecast['afternoon_desc']) ?> <?= $weatherDescToEmoji[$forecast['afternoon_desc']] ?></p>
                <p><strong>Note:</strong> <?= $forecast['note'] === "" ? "Nessuna nota" : htmlspecialchars($forecast['note']) ?></p>
            </div>
        </div>

        <form action="api/submit_plagiarism_report.php" method="POST">
            <input type="hidden" name="id" value="<?= htmlspecialchars($forecast['id']) ?>">
            <div class="form-group">
                <label for="comment">Motivazione della segnalazione</label>
                <textarea name="comment" id="comment" class="form-control" rows="5" required></textarea>
            </div>
            <button type="submit" class="btn btn-danger mt-3">Invia Segnalazione</button>
            <a href="students_forecasts.php" class="btn btn-secondary mt-3">Annulla</a>
        </form>
    </div>
    <script src="./assets/js/main.js"></script>
</body>

</html>
