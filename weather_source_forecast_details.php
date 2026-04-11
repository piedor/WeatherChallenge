<?php
    include 'utils/check_session.php';

    $message         = $_GET['message'] ?? null;
    $type            = $_GET['type'] ?? null;
    $weatherSourceId = $_GET['id'] ?? null;

    if (!$weatherSourceId) die("ID sorgente meteo non specificato.");

    // Nome e attribution sito meteo
    $stmt = $__con->prepare("SELECT name, attribution FROM weather_sources WHERE id = ?");
    $stmt->bind_param("i", $weatherSourceId);
    $stmt->execute();
    $weatherSource = $stmt->get_result()->fetch_assoc();

    // Previsioni
    $query = "SELECT * FROM weather_sources_forecasts WHERE weather_source_id = ? AND date <= CURDATE() ORDER BY date DESC";
    $stmt  = $__con->prepare($query);
    $stmt->bind_param("i", $weatherSourceId);
    $stmt->execute();
    $result = $stmt->get_result();

    $events    = [];
    $forecasts = [];

    while ($row = $result->fetch_assoc()) {
        $forecasts[]  = $row;
        $dateForecast = $row['date'];
        $dateToday    = date('Y-m-d');
        $idForecast   = $row['id'];

        $event = ['title' => 'Inserita', 'start' => $dateForecast, 'color' => '#0000FF'];

        if ($dateForecast === $dateToday) {
            $event['title'] = 'In corso';
            $event['color'] = '#FFD700';
        } elseif ($row['accuracy'] > 0 || $dateForecast < $dateToday) {
            $event['title'] = $row['accuracy'] . '%';
            $event['url']   = 'details_weather_source_forecasts.php?id=' . $idForecast;
        }

        if ($row['accuracy'] >= 60) {
            $event['color'] = '#008000';
        } elseif (($row['accuracy'] > 0 && $row['accuracy'] < 60) || ($row['accuracy'] == 0 && $dateForecast < $dateToday)) {
            $event['color'] = '#FF0000';
        }

        $events[] = $event;
    }
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Previsioni <?= htmlspecialchars($weatherSource['name']) ?></title>
    <meta name="description" content="WebApp previsioni meteo">
    <meta name="author" content="Pietro Dorighi">
    <link href="./favicon.ico" rel="shortcut icon" type="image/vnd.microsoft.icon">
    <?php require_once './utils/style.php'; ?>
    <link rel="stylesheet" href="./assets/css/style_app.css?v=<?php echo filemtime('assets/css/style_app.css'); ?>">
    <link rel="stylesheet" href="./assets/css/style_weather_source_forecast_details.css?v=<?php echo filemtime('assets/css/style_weather_source_forecast_details.css'); ?>">
    <script src="https://cdn.jsdelivr.net/npm/intro.js@7.2.0/intro.min.js"></script>
    <link  href="https://cdn.jsdelivr.net/npm/intro.js@7.2.0/minified/introjs.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="bg-light">
    <?php require('./utils/header.php'); ?>

    <?php if (!empty($message)):
        $alertClass = ($type === "success") ? "alert-success" : "alert-danger"; ?>
        <div id="messageAlert" class="alert <?= $alertClass ?> alert-dismissible fade show mx-auto" role="alert" style="max-width:1200px;">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi"></button>
        </div>
    <?php endif; ?>

    <div class="container mt-4">
        <h2 class="wsf-title mb-3">📅 Previsioni di <?= htmlspecialchars($weatherSource['name']) ?></h2>
        <?= $weatherSource['attribution'] ?>

        <!-- ══════ DESKTOP ══════ -->
        <div id="desktop-wsf">
            <div id="calendar" class="mt-3"></div>

            <table class="table table-striped mt-4">
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
                <?php foreach ($forecasts as $row):
                    $today        = new DateTime(); $today->setTime(0,0);
                    $forecastDate = new DateTime($row['date']); $forecastDate->setTime(0,0);
                    $isFuture     = $forecastDate >= $today;
                    $rowClass     = $isFuture ? 'table-warning' : '';
                ?>
                <tr class="<?= $rowClass ?>">
                    <td><?= htmlspecialchars($row['date']) ?></td>
                    <td><?= htmlspecialchars($row['morning_desc']) ?></td>
                    <td><?= htmlspecialchars($row['afternoon_desc']) ?></td>
                    <td><?= htmlspecialchars($row['temp_min']) ?>°C</td>
                    <td><?= htmlspecialchars($row['temp_max']) ?>°C</td>
                    <td><?= $isFuture ? '—' : htmlspecialchars($row['accuracy']) . '%' ?></td>
                    <td>
                        <?php if ($isFuture): ?>
                            <button class="btn btn-sm btn-secondary" disabled title="Non ancora valutata">⏳</button>
                        <?php else: ?>
                            <a class="btn btn-sm btn-outline-info" href="details_weather_source_forecasts.php?id=<?= $row['id'] ?>">🔍</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- ══════ MOBILE ══════ -->
        <div id="mobile-wsf">
            <div class="view-toggle mt-2">
                <button id="btn-wsf-grid" class="active" onclick="showWsfGrid()">📅 Griglia</button>
                <button id="btn-wsf-list" onclick="showWsfList()">📋 Lista</button>
            </div>

            <!-- Mini-calendario -->
            <div id="mini-wsf-wrap">
                <div id="mini-wsf-cal"></div>
            </div>

            <!-- Lista card -->
            <div id="list-wsf-wrap">
                <div class="wsf-list">
                <?php
                    $currentMonth = null;
                    foreach ($forecasts as $row):
                        $today        = new DateTime(); $today->setTime(0,0);
                        $forecastDate = new DateTime($row['date']); $forecastDate->setTime(0,0);
                        $isPast       = $forecastDate < $today;
                        $monthKey     = $forecastDate->format('Y-m');
                        $monthNames   = ['01'=>'Gennaio','02'=>'Febbraio','03'=>'Marzo','04'=>'Aprile',
                                         '05'=>'Maggio','06'=>'Giugno','07'=>'Luglio','08'=>'Agosto',
                                         '09'=>'Settembre','10'=>'Ottobre','11'=>'Novembre','12'=>'Dicembre'];

                        if ($monthKey !== $currentMonth):
                            $currentMonth = $monthKey;
                ?>
                    <div class="wsf-month-header">
                        <?= $monthNames[$forecastDate->format('m')] ?> <?= $forecastDate->format('Y') ?>
                    </div>
                <?php endif; ?>

                <?php
                    // Badge
                    $acc = $row['accuracy'];
                    if (!$isPast)        { $badgeClass = 'gray';  $badgeText = 'In corso'; }
                    elseif ($acc >= 60)  { $badgeClass = 'green'; $badgeText = $acc . '%'; }
                    else                 { $badgeClass = 'red';   $badgeText = $acc . '%'; }

                    // Emoji meteo
                    $morningEmoji   = $weatherDescToEmoji[$row['morning_desc']]   ?? $row['morning_desc'];
                    $afternoonEmoji = $weatherDescToEmoji[$row['afternoon_desc']] ?? $row['afternoon_desc'];
                ?>
                <div class="wsf-card">
                    <div class="wsf-card-top">
                        <span class="wsf-card-date"><?= $forecastDate->format('d/m/Y') ?></span>
                        <span class="wsf-badge <?= $badgeClass ?>"><?= $badgeText ?></span>
                    </div>
                    <div class="wsf-card-grid">
                        <div class="wsf-card-item">
                            <label>Mattina</label>
                            <span><?= $morningEmoji ?> <?= htmlspecialchars($row['morning_desc']) ?></span>
                        </div>
                        <div class="wsf-card-item">
                            <label>Pomeriggio</label>
                            <span><?= $afternoonEmoji ?> <?= htmlspecialchars($row['afternoon_desc']) ?></span>
                        </div>
                        <div class="wsf-card-item">
                            <label>Temp. Max</label>
                            <span class="temp-max"><?= htmlspecialchars($row['temp_max']) ?>°C</span>
                        </div>
                        <div class="wsf-card-item">
                            <label>Temp. Min</label>
                            <span class="temp-min"><?= htmlspecialchars($row['temp_min']) ?>°C</span>
                        </div>
                    </div>
                    <?php if ($isPast): ?>
                    <div class="wsf-card-actions">
                        <a href="details_weather_source_forecasts.php?id=<?= $row['id'] ?>"
                           class="btn btn-outline-info btn-sm">🔍 Dettagli</a>
                    </div>
                    <?php endif; ?>
                </div>

                <?php endforeach; ?>
                </div>
            </div>
        </div>

        <a href="weather_sources_forecasts.php" class="btn btn-secondary mt-3 <?= '' ?>">← Torna all'elenco siti meteo</a>
    </div>

    <script>
    const wsfEvents = <?= json_encode($events) ?>;

    function showWsfGrid() {
        document.getElementById('mini-wsf-wrap').classList.remove('hidden');
        document.getElementById('list-wsf-wrap').classList.remove('visible');
        document.getElementById('btn-wsf-grid').classList.add('active');
        document.getElementById('btn-wsf-list').classList.remove('active');
        if (wsfCalMini) wsfCalMini.updateSize();
    }
    function showWsfList() {
        document.getElementById('mini-wsf-wrap').classList.add('hidden');
        document.getElementById('list-wsf-wrap').classList.add('visible');
        document.getElementById('btn-wsf-list').classList.add('active');
        document.getElementById('btn-wsf-grid').classList.remove('active');
    }

    function makeWsfCalendar(el, aspectRatio) {
        return new FullCalendar.Calendar(el, {
            initialView:     'dayGridMonth',
            locale:          'it',
            aspectRatio:     aspectRatio,
            dayHeaderFormat: aspectRatio < 1 ? { weekday: 'narrow' } : { weekday: 'short' },
            buttonText:      { today: 'Oggi' },
            events:          wsfEvents,
            eventClick: function(info) {
                if (info.event.url) {
                    window.location.href = info.event.url;
                    info.jsEvent.preventDefault();
                }
            },
            eventDidMount: function(info) {
                if (info.event.extendedProps.description) {
                    const a = info.el.getElementsByClassName('fc-event-title');
                    if (a[0]) a[0].innerHTML = info.event.title + '<br>' + info.event.extendedProps.description;
                }
            },
            dayCellDidMount: function(info) {
                const today = new Date(); today.setHours(0,0,0,0);
                info.el.style.cursor = 'pointer';
                info.el.onclick = function() {
                    const d  = info.date;
                    if (d <= today) return;
                    const ds = d.getFullYear() + '-' +
                               String(d.getMonth()+1).padStart(2,'0') + '-' +
                               String(d.getDate()).padStart(2,'0');
                    const cal = info.view.calendar;
                    const evs = cal.getEvents().filter(e => e.startStr === ds);
                    if (evs.length > 0 && evs[0].url) window.location.href = evs[0].url;
                };
            },
        });
    }

    let wsfCalDesktop = null;
    let wsfCalMini    = null;

    document.addEventListener('DOMContentLoaded', function() {
        if (window.innerWidth > 768) {
            wsfCalDesktop = makeWsfCalendar(document.getElementById('calendar'), 1.35);
            wsfCalDesktop.render();
        } else {
            wsfCalMini = makeWsfCalendar(document.getElementById('mini-wsf-cal'), 0.9);
            wsfCalMini.render();
        }
    });

    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (window.innerWidth > 768 && !wsfCalDesktop) {
                wsfCalDesktop = makeWsfCalendar(document.getElementById('calendar'), 1.35);
                wsfCalDesktop.render();
            }
            else{
                if (wsfCalDesktop) {
                    wsfCalDesktop.destroy();
                    wsfCalDesktop = null;
                }
                if (!wsfCalMini) {
                    wsfCalMini = makeWsfCalendar(document.getElementById('mini-wsf-cal'), 0.9);
                    wsfCalMini.render();
                }
            }
        }, 200);
    });
    </script>
    <script src="./assets/js/main.js?v=2"></script>
</body>
</html>