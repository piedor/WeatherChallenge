<?php
    // Include il file per il controllo della sessione
    include '../utils/check_session.php';

    // Solo admin può vedere questa pagina
    if ($role !== 'admin') {
        redirectToErrorPage(403);
        exit;
    }

    // Recupera tutte le segnalazioni
    $query = "SELECT pr.*, u1.full_name AS reporter_name, u2.full_name AS reported_name
            FROM plagiarism_reports pr
            JOIN users u1 ON pr.reported_by = u1.id
            JOIN users u2 ON pr.reported_user_id = u2.id
            ORDER BY pr.report_date DESC";

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
    <link href="../favicon.ico" rel="shortcut icon" type="image/vnd.microsoft.icon">
    <?php require_once '../utils/style.php'; ?>
    <link rel="stylesheet" href="../assets/css/style_app.css">
    <link rel="stylesheet" href="../assets/css/style_dashboard.css">
</head>
<body class="bg-light">
    <?php require ('../utils/header.php'); ?>
    <!-- Eventuali messaggi di errore/successo -->
    <?php if (!empty($message)): 
            $alertClass = ($type === "success") ? "alert-success" : "alert-danger";
    ?>
        <div id="messageAlert" class="alert <?= $alertClass ?> alert-dismissible fade show mx-auto" role="alert" style="max-width: 1200px;">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi"></button>
        </div>
    <?php endif; ?>
    <div class="container">
        <h2 class="mb-4">Gestione Segnalazioni di Plagio</h2>

        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Forecast ID</th>
                    <th>Previsione</th>
                    <th>Segnalato da</th>
                    <th>Studente</th>
                    <th>Commento</th>
                    <th>Data</th>
                    <th>Stato</th>
                    <th>Esito</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($report = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($report['forecast_id']) ?></td>
                        <td><a href="../details_forecast.php?id=<?= $report['forecast_id'] ?>" target="_blank">Vai</a></td>
                        <td><?= htmlspecialchars($report['reporter_name']) ?></td>
                        <td><?= htmlspecialchars($report['reported_name']) ?></td>
                        <td><?= htmlspecialchars($report['comment']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($report['report_date'])) ?></td>
                        
                        <!-- Stato -->
                        <td>
                            <?php
                                $statusClass = '';
                                switch ($report['status']) {
                                    case 'open':
                                        $statusClass = 'badge bg-warning text-dark';
                                        break;
                                    case 'reviewing':
                                        $statusClass = 'badge bg-info text-dark';
                                        break;
                                    case 'closed':
                                        $statusClass = 'badge bg-secondary';
                                        break;
                                    default:
                                        $statusClass = 'badge bg-light text-dark';
                                }
                            ?>
                            <span class="<?= $statusClass ?>"><?= strtoupper($report['status']) ?></span>
                        </td>

                        <!-- Esito -->
                        <td>
                            <?php if ($report['outcome'] === 'confirmed'): ?>
                                <span class="badge bg-success">✅ Confermato</span>
                            <?php elseif ($report['outcome'] === 'dismissed'): ?>
                                <span class="badge bg-danger">❌ Nessun Plagio</span>
                            <?php else: ?>
                                <span class="badge bg-light text-dark">--</span>
                            <?php endif; ?>
                        </td>

                        <!-- Azioni -->
                        <td>
                            <?php if ($report['status'] === 'open'): ?>
                                <button onclick="updateReportStatus(<?= $report['id'] ?>, 'reviewing')" class="btn btn-info btn-sm">Prendi in carico</button>
                            <?php elseif ($report['status'] === 'reviewing'): ?>
                                <button onclick="updateReportStatus(<?= $report['id'] ?>, 'closed', 'confirmed')" class="btn btn-success btn-sm">Conferma Plagio</button>
                                <button onclick="updateReportStatus(<?= $report['id'] ?>, 'closed', 'dismissed')" class="btn btn-secondary btn-sm">Nessun Plagio</button>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <script>
        function updateReportStatus(reportId, status, outcome = null) {
            const formData = new FormData();
            formData.append('report_id', reportId);
            formData.append('status', status);
            if (outcome !== null) {
                formData.append('outcome', outcome);
            }

            fetch('../api/update_report_status.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Aggiorna pagina (o aggiorna tabella senza reload, se vuoi)
                    location.reload();
                } else {
                    alert('Errore: ' + (data.error || 'Impossibile aggiornare.'));
                }
            })
            .catch(error => {
                alert('Errore di rete.');
                console.error('Errore:', error);
            });
        }
    </script>
    <script src="../assets/js/main.js"></script>
</body>
</html>
