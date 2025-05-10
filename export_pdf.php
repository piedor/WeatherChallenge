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
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">📄 Esporta le tue Previsioni in PDF</h4>
            </div>
            <div class="card-body">
                <form id="pdfForm">
                    <div class="mb-3">
                        <label for="start_date" class="form-label">Data Inizio:</label>
                        <input type="date" id="start_date" name="start_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="end_date" class="form-label">Data Fine:</label>
                        <input type="date" id="end_date" name="end_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="sintesi">Sintesi (opzionale):</label><br/>
                        <textarea id="sintesi" name="sintesi" rows="4" placeholder="Inserisci una sintesi delle tue previsioni..."></textarea>
                    </div>
                    <button type="button" class="btn btn-success" id="downloadPDF">📥 Scarica PDF</button>
                </form>
            </div>
        </div>
    </div>
    <script>
        document.getElementById("downloadPDF").addEventListener("click", function () {
            let startDate = document.getElementById("start_date").value;
            let endDate = document.getElementById("end_date").value;
            let sintesi = document.getElementById("sintesi").value;

            if (startDate && endDate) {
                window.location.href = "api/generate_pdf.php?start_date=" + startDate + "&end_date=" + endDate + "&sintesi=" + sintesi;
            } else {
                alert("Seleziona un intervallo di date!");
            }
        });
    </script>
    <script src="./assets/js/main.js"></script>
</body>
</html>
