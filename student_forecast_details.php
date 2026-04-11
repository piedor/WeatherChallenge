<?php
    include 'utils/check_session.php';

    $message = $_GET['message'] ?? null;
    $type    = $_GET['type'] ?? null;

    if ($role !== 'professor' && $role !== 'admin') {
        header('Location: index.php'); exit;
    }

    $studentId = $_GET['id'] ?? null;
    if (!$studentId) die("ID studente non specificato.");

    // Nome studente
    $stmt = $__con->prepare("SELECT full_name FROM users WHERE id = ?");
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();

    // Previsioni
    $query = "SELECT * FROM forecasts WHERE user_id = ? ORDER BY date DESC";
    $stmt  = $__con->prepare($query);
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();

    $events    = [];
    $forecasts = [];

    while ($row = $result->fetch_assoc()) {
        // Controllo plagiarism
        $stmtP = $__con->prepare("SELECT 1 FROM plagiarism_reports WHERE forecast_id = ? LIMIT 1");
        $stmtP->bind_param("i", $row['id']);
        $stmtP->execute();
        $row['is_reported'] = $stmtP->get_result()->num_rows > 0;

        $forecasts[] = $row;

        $dateForecast = $row['date'];
        $dateToday    = date('Y-m-d');
        $idForecast   = $row['id'];

        $event = ['title' => 'Inserita', 'start' => $dateForecast, 'color' => '#0000FF'];

        if ($dateForecast === $dateToday) {
            $event['title'] = 'In corso';
            $event['color'] = '#FFD700';
        } elseif ($row['accuracy'] > 0 || $dateForecast < $dateToday) {
            $event['title'] = $row['accuracy'] . '%' . ($row['is_reported'] ? ' ⚠️' : '');
            $event['url']   = 'details_forecast.php?id=' . $idForecast . '&from=students';
        }

        if ($row['note'] !== '') $event['description'] = '📝 Nota presente';

        if ($row['accuracy'] >= 60) {
            $event['color'] = '#008000';
        } elseif (($row['accuracy'] > 0 && $row['accuracy'] < 60) || ($row['accuracy'] == 0 && $dateForecast < $dateToday)) {
            $event['color'] = '#FF0000';
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
    <title>Previsioni <?= htmlspecialchars($student['full_name']) ?></title>
    <meta name="description" content="WebApp previsioni meteo">
    <meta name="author" content="Pietro Dorighi">
    <link href="./favicon.ico" rel="shortcut icon" type="image/vnd.microsoft.icon">
    <?php require_once './utils/style.php'; ?>
    <link rel="stylesheet" href="./assets/css/style_app.css?v=<?php echo filemtime('assets/css/style_app.css'); ?>">
    <link rel="stylesheet" href="./assets/css/style_student_forecast_details.css?v=<?php echo filemtime('assets/css/style_student_forecast_details.css'); ?>">
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
        <h2 class="sfd-title mb-3">📅 Previsioni di <?= htmlspecialchars($student['full_name']) ?></h2>

        <!-- ══════ DESKTOP ══════ -->
        <div id="desktop-sfd">
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
                    $rowClass     = $isFuture ? 'table-warning' : ($row['is_reported'] ? 'table-danger' : '');
                ?>
                <tr class="<?= $rowClass ?>">
                    <td><?= htmlspecialchars($row['date']) ?></td>
                    <td><?= htmlspecialchars($row['morning_desc']) ?></td>
                    <td><?= htmlspecialchars($row['afternoon_desc']) ?></td>
                    <td><?= htmlspecialchars($row['temp_min']) ?>°C</td>
                    <td><?= htmlspecialchars($row['temp_max']) ?>°C</td>
                    <td>
                        <?= $isFuture ? '—' : htmlspecialchars($row['accuracy']) . '%' ?>
                        <?= $row['is_reported'] ? ' ⚠️' : '' ?>
                    </td>
                    <td>
                        <?php if ($isFuture): ?>
                            <button class="btn btn-sm btn-secondary" disabled title="Non ancora valutata">⏳</button>
                        <?php else: ?>
                            <a class="btn btn-sm btn-outline-info" href="details_forecast.php?id=<?= $row['id'] ?>&from=students">🔍</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- ══════ MOBILE ══════ -->
        <div id="mobile-sfd">
            <div class="view-toggle mt-2">
                <button id="btn-sfd-grid" class="active" onclick="showSfdGrid()">📅 Griglia</button>
                <button id="btn-sfd-list" onclick="showSfdList()">📋 Lista</button>
            </div>

            <div id="mini-sfd-wrap">
                <div id="mini-sfd-cal"></div>
            </div>

            <div id="list-sfd-wrap">
                <div class="sfd-list">
                <?php
                    $currentMonth = null;
                    $monthNames   = ['01'=>'Gennaio','02'=>'Febbraio','03'=>'Marzo','04'=>'Aprile',
                                     '05'=>'Maggio','06'=>'Giugno','07'=>'Luglio','08'=>'Agosto',
                                     '09'=>'Settembre','10'=>'Ottobre','11'=>'Novembre','12'=>'Dicembre'];

                    foreach ($forecasts as $row):
                        $today        = new DateTime(); $today->setTime(0,0);
                        $forecastDate = new DateTime($row['date']); $forecastDate->setTime(0,0);
                        $isPast       = $forecastDate < $today;
                        $isFuture     = $forecastDate >= $today;
                        $monthKey     = $forecastDate->format('Y-m');

                        if ($monthKey !== $currentMonth):
                            $currentMonth = $monthKey;
                ?>
                    <div class="sfd-month-header">
                        <?= $monthNames[$forecastDate->format('m')] ?> <?= $forecastDate->format('Y') ?>
                    </div>
                <?php endif;

                    // Badge
                    $acc = $row['accuracy'];
                    if ($row['is_reported'] && $isPast) {
                        $badgeClass = 'orange'; $badgeText = $acc . '% ⚠️';
                    } elseif (!$isPast) {
                        $badgeClass = 'gray';   $badgeText = $isFuture ? 'Futura' : 'In corso';
                    } elseif ($acc >= 60) {
                        $badgeClass = 'green';  $badgeText = $acc . '%';
                    } else {
                        $badgeClass = 'red';    $badgeText = $acc . '%';
                    }

                    $morningEmoji   = $weatherDescToEmoji[$row['morning_desc']]   ?? $row['morning_desc'];
                    $afternoonEmoji = $weatherDescToEmoji[$row['afternoon_desc']] ?? $row['afternoon_desc'];
                ?>
                <div class="sfd-card">
                    <div class="sfd-card-top">
                        <span class="sfd-card-date"><?= $forecastDate->format('d/m/Y') ?></span>
                        <span class="sfd-badge <?= $badgeClass ?>"><?= $badgeText ?></span>
                    </div>
                    <div class="sfd-card-grid">
                        <div class="sfd-card-item">
                            <label>Mattina</label>
                            <span><?= $morningEmoji ?> <?= htmlspecialchars($row['morning_desc']) ?></span>
                        </div>
                        <div class="sfd-card-item">
                            <label>Pomeriggio</label>
                            <span><?= $afternoonEmoji ?> <?= htmlspecialchars($row['afternoon_desc']) ?></span>
                        </div>
                        <div class="sfd-card-item">
                            <label>Temp. Max</label>
                            <span class="temp-max"><?= htmlspecialchars($row['temp_max']) ?>°C</span>
                        </div>
                        <div class="sfd-card-item">
                            <label>Temp. Min</label>
                            <span class="temp-min"><?= htmlspecialchars($row['temp_min']) ?>°C</span>
                        </div>
                        <?php if ($isPast): ?>
                        <div class="sfd-card-item">
                            <label>Nota</label>
                            <span><?= !empty($row['note']) ? '📝 Sì' : '—' ?></span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($row['note'])): ?>
                        <div class="sfd-card-note">📝 <?= htmlspecialchars(mb_strimwidth($row['note'], 0, 60, '…')) ?></div>
                    <?php endif; ?>

                    <?php if ($isPast): ?>
                    <div class="sfd-card-actions">
                        <a href="details_forecast.php?id=<?= $row['id'] ?>&from=students"
                           class="btn btn-outline-info btn-sm">🔍 Dettagli</a>
                    </div>
                    <?php endif; ?>
                </div>

                <?php endforeach; ?>
                </div>
            </div>
        </div>

        <a href="students_forecasts.php" class="btn btn-secondary mt-3">← Torna all'elenco studenti</a>
    </div>

    <script>
    const sfdEvents = <?= json_encode($events) ?>;

    function showSfdGrid() {
        document.getElementById('mini-sfd-wrap').classList.remove('hidden');
        document.getElementById('list-sfd-wrap').classList.remove('visible');
        document.getElementById('btn-sfd-grid').classList.add('active');
        document.getElementById('btn-sfd-list').classList.remove('active');
        if (sfdCalMini) sfdCalMini.updateSize();
    }
    function showSfdList() {
        document.getElementById('mini-sfd-wrap').classList.add('hidden');
        document.getElementById('list-sfd-wrap').classList.add('visible');
        document.getElementById('btn-sfd-list').classList.add('active');
        document.getElementById('btn-sfd-grid').classList.remove('active');
    }

    function makeSfdCalendar(el, aspectRatio) {
        return new FullCalendar.Calendar(el, {
            initialView:     'dayGridMonth',
            locale:          'it',
            aspectRatio:     aspectRatio,
            dayHeaderFormat: aspectRatio < 1 ? { weekday: 'narrow' } : { weekday: 'short' },
            buttonText:      { today: 'Oggi' },
            events:          sfdEvents,
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
                    const d = info.date;
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

    let sfdCalDesktop = null;
    let sfdCalMini    = null;

    document.addEventListener('DOMContentLoaded', function() {
        if (window.innerWidth > 768) {
            sfdCalDesktop = makeSfdCalendar(document.getElementById('calendar'), 1.35);
            sfdCalDesktop.render();
        } else {
            sfdCalMini = makeSfdCalendar(document.getElementById('mini-sfd-cal'), 0.9);
            sfdCalMini.render();
        }
    });

    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (window.innerWidth > 768 && !sfdCalDesktop) {
                sfdCalDesktop = makeSfdCalendar(document.getElementById('calendar'), 1.35);
                sfdCalDesktop.render();
            }
            else if (window.innerWidth <= 768 && !sfdCalMini) {
                sfdCalMini = makeSfdCalendar(document.getElementById('mini-sfd-cal'), 0.9);
                sfdCalMini.render();
            }
        }, 200);
    });
    </script>
    <script src="./assets/js/main.js?v=2"></script>
</body>
</html>