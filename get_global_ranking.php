<?php
    // Connessione al database
    include 'utils/db_connection.php';

    // Query per recuperare gli utenti con almeno 5 previsioni valutate
    $query = "
        SELECT u.full_name, u.score
        FROM users u
        WHERE (
            SELECT COUNT(*) 
            FROM forecasts f 
            WHERE f.user_id = u.id AND f.accuracy != 0 AND f.accuracy IS NOT NULL
        ) >= 5
        AND u.score IS NOT NULL
        ORDER BY u.score DESC";

    $stmt = $__con->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();

    $ranking = [];

    while ($row = $result->fetch_assoc()) {
        $ranking[] = [
            'full_name' => $row['full_name'],
            'score' => round($row['score'], 2)
        ];
    }

    // Output JSON
    header('Content-Type: application/json');
    echo json_encode($ranking);
?>
