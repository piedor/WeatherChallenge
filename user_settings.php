<?php
    // Include il file per il controllo della sessione
    include 'utils/check_session.php';

    // Messaggio di conferma o errore
    $message = $_GET['message'] ?? null;
    $type = $_GET['type'] ?? null; // "success" o "error"

    // Recupera il nome del meteo dal database
    $query = "SELECT forecast_name FROM users WHERE id = ?";
    $stmt = $__con->prepare($query);

    if (!$stmt) {
        die("Errore nella preparazione della query: " . $__con->error);
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $forecastName = $row['forecast_name'] ?? ''; // Usa il valore dal database, se esiste

    // Se l'utente invia il form, aggiorna il nome del meteo nel database
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $forecastName = trim($_POST["forecast_name"]);

        $query = "UPDATE users SET forecast_name = ? WHERE id = ?";
        $stmt = $__con->prepare($query);
        $stmt->bind_param("si", $forecastName, $user_id);
        $stmt->execute();

        header("Location: user_settings.php?success=1");
        exit;
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
        <h2 class="text-center">⚙️ Impostazioni Profilo</h2>
        <?php if (isset($_GET['success'])): ?>
            <p style="color: green;">Nome del meteo aggiornato con successo!</p>
        <?php endif; ?>
        
        <form method="POST">
            <label for="forecast_name">Nome del tuo meteo:</label>
            <input type="text" id="forecast_name" name="forecast_name" value="<?= htmlspecialchars($forecastName ?? '') ?>" required>
            <button type="submit">Salva</button>
        </form>
    </div>
    <script src="./assets/js/main.js"></script>
</body>
</html>
