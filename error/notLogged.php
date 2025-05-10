<?php http_response_code(401); ?>
<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Autenticazione richiesta - Stazione Meteo</title>
        <?php require_once '../utils/style.php'; ?>
    </head>
    <body>
        <div class="d-flex align-items-center justify-content-center vh-100">
            <div class="text-center">
                <h1 class="display-1 fw-bold">🔒 Autenticazione richiesta</h1>
                <p class="fs-3"> <span class="text-danger">Devi effettuare l'accesso per accedere a questa pagina.</span></p>
                <a href="../index.php" class="btn btn-primary">Torna alla home</a>
            </div>
        </div>
    </body>
</html>