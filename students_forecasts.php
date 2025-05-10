<?php
    // Include il file per il controllo della sessione
    include 'utils/check_session.php';

    // Messaggio di conferma o errore
    $message = $_GET['message'] ?? null;
    $type = $_GET['type'] ?? null; // "success" o "error"

    // Solo i professori e admin possono accedere
    if ($role !== 'professor' && $role !== 'admin') {
        header('Location: index.php');
        exit;
    }

    $query = "SELECT id, full_name, total_accuracy FROM users WHERE role = 'student' ORDER BY total_accuracy DESC";
    $result = $__con->query($query);

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
    <!-- Intro.js -->
    <script src="https://cdn.jsdelivr.net/npm/intro.js@7.2.0/intro.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/intro.js@7.2.0/minified/introjs.min.css" rel="stylesheet">
    <!-- JQuery.js -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
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
        <h2 class="text-center mb-4">📋 Elenco Studenti - Classifica Accuratezza</h2>
        <ul class="list-group">
            <?php while ($row = $result->fetch_assoc()): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <a href="student_forecast_details.php?id=<?= $row['id'] ?>" class="text-decoration-none">
                        <?= htmlspecialchars($row['full_name']) ?>
                    </a>
                    <span class="badge bg-primary rounded-pill"><?= round($row['total_accuracy'], 2) ?>%</span>
                </li>
            <?php endwhile; ?>
        </ul>
        <br>
        <a href="api/generate_student_comparison_pdf.php" class="btn btn-outline-success mb-3">
            📥 Scarica PDF confronto studenti
        </a>
    </div>
    <script src="./assets/js/main.js"></script>
</body>

</html>
