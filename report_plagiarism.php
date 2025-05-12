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
        
    </div>
    <script src="./assets/js/main.js"></script>
</body>

</html>
