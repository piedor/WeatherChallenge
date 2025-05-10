<!DOCTYPE html>
<html lang="it">

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Errore - Stazione Meteo</title>
        <?php require_once '../utils/style.php'; ?>
    </head>

    <body>
        <div class="d-flex align-items-center justify-content-center vh-100">
            <div class="text-center">
                <p class="fs-3"> <span class="text-danger">Opps!</span> Si è verificato un errore</p>
                <p class="lead">
                    <?= htmlspecialchars($_GET['message'] ?? "Si è verificato un problema.") ?>
                </p>
                <a href="../index.php" class="btn btn-primary">Torna alla home</a>
            </div>
        </div>
    </body>

</html>