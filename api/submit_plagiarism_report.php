<?php
    include '../utils/check_session.php';

    // Controlla che arrivi con metodo POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirectToErrorPage(0, "Metodo di accesso non consentito");
        exit;
    }

    // Controllo dei dati ricevuti
    if (!isset($_POST['id']) || !isset($_POST['comment'])) {
        redirectToErrorPage(0, "Dati mancanti");
        exit;
    }

    $forecast_id = intval($_POST['id']);
    $comment = trim($_POST['comment']);

    // Verifica che la previsione esista davvero
    $stmt = $__con->prepare("SELECT user_id FROM forecasts WHERE id = ?");
    $stmt->bind_param("i", $forecast_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $stmt->close();
        redirectToErrorPage(0, "Previsione non trovata");
        exit;
    }

    $stmt->bind_result($reported_user_id);
    $stmt->fetch();
    $stmt->close();

    if (isset($role) && ($role !== 'professor' && $role !== 'admin')){
        redirectToErrorPage(403);
    }

    // Impedisci che si segnali se stessi (ulteriore protezione lato backend)
    if ($reported_user_id == $user_id) {
        redirectToErrorPage(0, "Non puoi segnalare te stesso");
        exit;
    }

    // Controllo se esiste già una segnalazione
    $query = "SELECT COUNT(*) FROM plagiarism_reports WHERE forecast_id = ? AND reported_by = ?";
    $stmt = $__con->prepare($query);
    $stmt->bind_param("ii", $forecast_id, $user_id);
    $stmt->execute();
    $stmt->bind_result($alreadyReported);
    $stmt->fetch();
    $stmt->close();

    if ($alreadyReported > 0) {
        header('Location: ../students_forecasts.php?message=Hai già segnalato questa previsione&type=error');
        exit;
    }

    // Inserisci la segnalazione nel database
    $stmt = $__con->prepare("INSERT INTO plagiarism_reports (forecast_id, reported_by, reported_user_id, comment) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $forecast_id, $user_id, $reported_user_id, $comment);
    $stmt->execute();
    $stmt->close();

    // Torna alla lista previsioni con messaggio di successo
    header('Location: ../students_forecasts.php?message=Segnalazione inviata con successo&type=success');
    exit;
?>
