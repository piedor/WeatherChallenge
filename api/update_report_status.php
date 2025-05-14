<?php
    header("Content-Type: application/json");
    // Controlla sessione e ruolo
    include '../utils/check_session.php';

    // Solo admin può aggiornare segnalazioni
    if ($role !== 'admin') {
        echo json_encode(["error" => "Accesso negato."]);
        exit;
    }

    // Solo tramite POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(["error" => "Metodo non consentito."]);
        exit;
    }

    // Ricevi i dati
    $reportId = intval($_POST['report_id'] ?? 0);
    $newStatus = $_POST['status'] ?? '';
    $outcome = $_POST['outcome'] ?? null;

    // Validazione base
    $allowedStatuses = ['open', 'reviewing', 'closed'];
    $allowedOutcomes = ['confirmed', 'dismissed', null];

    if (!in_array($newStatus, $allowedStatuses)) {
        echo json_encode(["error" => "Stato non valido.".$newStatus.$reportId]);
        exit;
    }

    if (!in_array($outcome, $allowedOutcomes)) {
        echo json_encode(["error" => "Esito finale non valido."]);
        exit;
    }

    // Aggiorna nel database
    if ($newStatus === 'closed' && $outcome !== null) {
        $query = "UPDATE plagiarism_reports SET status = ?, outcome = ? WHERE id = ?";
        $stmt = $__con->prepare($query);
        $stmt->bind_param("ssi", $newStatus, $outcome, $reportId);
    } else {
        $query = "UPDATE plagiarism_reports SET status = ? WHERE id = ?";
        $stmt = $__con->prepare($query);
        $stmt->bind_param("si", $newStatus, $reportId);
    }

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["error" => "Errore durante l'aggiornamento della segnalazione."]);
    }
?>
