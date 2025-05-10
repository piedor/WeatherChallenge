<?php http_response_code(404); ?>
<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Pagina non trovata - Stazione Meteo</title>
        <?php require_once '../utils/style.php'; ?>
    </head>
    <body>
        <div class="d-flex align-items-center justify-content-center vh-100">
            <div class="text-center">
                <h1 class="display-1 fw-bold">❌ Pagina non trovata</h1>
                <p class="fs-3"> <span class="text-danger">La pagina che stai cercando non esiste.</span></p>
                <a href="../index.php" class="btn btn-primary">Torna alla home</a>
            </div>
        </div>
    </body>
</html>