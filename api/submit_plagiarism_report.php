<?php
    header("Content-Type: application/json");
    include '../utils/check_session.php';

    // Consenti solo richieste POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405); // Metodo non consentito
        echo json_encode(["error" => "Metodo di accesso non consentito"]);
        exit;
    }

    // Controlla che i dati siano presenti
    if (!isset($_POST['id']) || !isset($_POST['comment'])) {
        http_response_code(400); // Bad Request
        echo json_encode(["error" => "Dati mancanti"]);
        exit;
    }

    $forecast_id = intval($_POST['id']);
    $comment = trim($_POST['comment']);

    // Verifica che la previsione esista
    $stmt = $__con->prepare("SELECT user_id FROM forecasts WHERE id = ?");
    $stmt->bind_param("i", $forecast_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $stmt->close();
        http_response_code(404); // Not Found
        echo json_encode(["error" => "Previsione non trovata"]);
        exit;
    }

    $stmt->bind_result($reported_user_id);
    $stmt->fetch();
    $stmt->close();

    // Solo professor o admin possono segnalare
    if (isset($role) && ($role !== 'professor' && $role !== 'admin')) {
        http_response_code(403); // Forbidden
        echo json_encode(["error" => "Accesso non autorizzato"]);
        exit;
    }

    // Non puoi segnalare te stesso
    if ($reported_user_id == $user_id) {
        http_response_code(400); // Bad Request
        echo json_encode(["error" => "Non puoi segnalare te stesso"]);
        exit;
    }

    // Verifica se già segnalato
    $query = "SELECT COUNT(*) FROM plagiarism_reports WHERE forecast_id = ? AND reported_by = ?";
    $stmt = $__con->prepare($query);
    $stmt->bind_param("ii", $forecast_id, $user_id);
    $stmt->execute();
    $stmt->bind_result($alreadyReported);
    $stmt->fetch();
    $stmt->close();

    if ($alreadyReported > 0) {
        http_response_code(409); // Conflict
        echo json_encode(["error" => "Hai già segnalato questa previsione"]);
        exit;
    }

    // Inserisci la segnalazione
    $stmt = $__con->prepare("INSERT INTO plagiarism_reports (forecast_id, reported_by, reported_user_id, comment) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $forecast_id, $user_id, $reported_user_id, $comment);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        http_response_code(500); // Server error
        echo json_encode(["error" => "Errore durante l'invio della segnalazione"]);
    }
    $stmt->close();
?>