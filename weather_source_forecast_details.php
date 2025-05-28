<?php
    // Include il file per il controllo della sessione
    include 'utils/check_session.php';

    // Messaggio di conferma o errore
    $message = $_GET['message'] ?? null;
    $type = $_GET['type'] ?? null; // "success" o "error"

    $weatherSourceId = $_GET['id'] ?? null;

    if (!$weatherSourceId) {
        die("ID sorgente meteo non specificato.");
    }

    // Recupera nome del sito meteo
    $stmt = $__con->prepare("SELECT name, attribution FROM weather_sources WHERE id = ?");
    $stmt->bind_param("i", $weatherSourceId);
    $stmt->execute();
    $result = $stmt->get_result();
    $weatherSource = $result->fetch_assoc();

    // Recupera previsioni
    $query = "SELECT * FROM weather_sources_forecasts WHERE weather_source_id = ? AND date <= CURDATE() ORDER BY date DESC";
    $stmt = $__con->prepare($query);
    $stmt->bind_param("i", $weatherSourceId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $color = '#0000FF'; // Blu di default (solo previsione inserita)
        $title = "Inserita"; // Titolo di default
        $dateForecast = $row['date'];
        $dateToday = date('Y-m-d');
        $idForecast = $row['id'];
    
        $event = [
            'title' => $title,
            'start' => $dateForecast,
            'color' => $color
        ];
    
        // Se la previsione è per oggi
        if ($dateForecast === $dateToday) {
            $event['title'] = "In corso";
            $event['color'] = '#FFD700';
            // NON aggiungere URL
        } elseif ($row['accuracy'] > 0 || $dateForecast < $dateToday) {
            $event['title'] = $row['accuracy'] . "%";
            $event['url'] = "details_weather_source_forecasts.php?id=" . $idForecast;
        }
    
        // Colore in base all'accuratezza
        if ($row['accuracy'] >= 60) {
            $event['color'] = '#008000'; // Verde
        } elseif (($row['accuracy'] > 0 && $row['accuracy'] < 60) || ($row['accuracy'] == 0 && $dateForecast < $dateToday)) {
            $event['color'] = '#FF0000'; // Rosso
        }
    
        $events[] = $event;
    }
    
    $result->data_seek(0);

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
    <!-- Fullcalendar.js -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
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
        <h2 class="mb-4">📅 Previsioni di <?= htmlspecialchars($weatherSource['name']) ?></h2>
        <?php echo $weatherSource['attribution'] ?>

        <div id="calendar"></div>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Meteo Mattina</th>
                    <th>Meteo Pomeriggio</th>
                    <th>Min</th>
                    <th>Max</th>
                    <th>Accuratezza</th>
                    <th>Dettagli</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()):
                $today = new DateTime();
                $today->setTime(0, 0);
                $forecastDate = new DateTime($row['date']);
                $forecastDate->setTime(0, 0);
                $isFuture = $forecastDate >= $today;
                $rowClass = $isFuture ? 'table-warning' : ''; // Giallo chiaro se futura
            ?>
            <tr class="<?= $rowClass ?>">
                <td><?= htmlspecialchars($row['date']) ?></td>
                <td><?= htmlspecialchars($row['morning_desc']) ?></td>
                <td><?= htmlspecialchars($row['afternoon_desc']) ?></td>
                <td><?= htmlspecialchars($row['temp_min']) ?>°C</td>
                <td><?= htmlspecialchars($row['temp_max']) ?>°C</td>
                <td>
                    <?= $isFuture ? '—' : htmlspecialchars($row['accuracy']) . '%' ?>
                </td>
                <td>
                    <?php if ($isFuture): ?>
                        <button class="btn btn-sm btn-secondary" disabled title="Previsione non ancora valutata">⏳</button>
                    <?php else: ?>
                        <a class="btn btn-sm btn-outline-info" href="details_weather_source_forecasts.php?id=<?= $row['id'] ?>">🔍</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>

        <a href="weather_sources_forecasts.php" class="btn btn-secondary">← Torna all'elenco siti meteo</a>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'it',
                buttonText: {
                    today: "Oggi" // Traduzione del pulsante "today"
                },
                events: <?= json_encode($events) ?>,
                eventClick: function(info) {
                    if (info.event.url) {
                        window.location.href = info.event.url;
                        info.jsEvent.preventDefault();
                    }
                },
                eventDidMount: function (info) {
                    // If a description exists add as second line to title
                    if ( info.event.extendedProps.description != '' &&
                        typeof info.event.extendedProps.description !== 'undefined'
                    ) {
                        const a = info.el.getElementsByClassName('fc-event-title');
                        a[0].innerHTML = info.event.title +  '<br>' + info.event.extendedProps.description;
                    }
                },
                dayCellDidMount: function(info) {
                    const today = new Date();
                    today.setHours(0, 0, 0, 0); // oggi alle ore 00:00
                    const clickedDate = info.date;

                    info.el.style.cursor = 'pointer';
                    info.el.onclick = function() {
                        const dateStr = clickedDate.getFullYear() + '-' +
                        String(clickedDate.getMonth() + 1).padStart(2, '0') + '-' +
                        String(clickedDate.getDate()).padStart(2, '0');

                        const events = calendar.getEvents().filter(event => event.startStr === dateStr);
                        if (clickedDate <= today) {
                            // Non fare niente se la data è oggi o passata
                            return;
                        }

                        if (events.length > 0){
                            // Se evento già presente, vai alla sua pagina
                            const eventUrl = events[0].url;
                            if (eventUrl) {
                                window.location.href = eventUrl;
                            }
                        }
                    };
                },
            });
            calendar.render();
        });
    </script>
    <script src="./assets/js/main.js"></script>
</body>

</html>
