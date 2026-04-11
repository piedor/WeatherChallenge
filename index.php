<?php
    include 'utils/check_session.php';

    $message    = $_GET['message'] ?? null;
    $type       = $_GET['type'] ?? null;
    $start_date = date('Y-m-d', strtotime('+1 days'));
    $end_date   = date('Y-m-d', strtotime('+7 days'));

    $query = "SELECT id, date, temperature, morning_desc, afternoon_desc, accuracy, note FROM forecasts WHERE user_id = ? ORDER BY date DESC";
    $stmt  = $__con->prepare($query);

    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $events = [];

        while ($row = $result->fetch_assoc()) {
            $color        = '#0000FF';
            $title        = "Inserita";
            $dateForecast = $row['date'];
            $dateToday    = date('Y-m-d');
            $idForecast   = $row['id'];
            $description  = "";
            $url          = "modify_forecast.php?id=" . $idForecast;

            if ($dateForecast === $dateToday) {
                $title = "In corso";
                $color = '#FFD700';
                $url   = "";
            } elseif ($row['accuracy'] > 0 || $dateForecast < $dateToday) {
                $title = $row['accuracy'] . "%";
                $url   = "details_forecast.php?id=" . $idForecast;
            }

            if ($row['note'] !== "") {
                $description = "📝 Nota presente";
            }

            if ($row['accuracy'] >= 60) {
                $color = '#008000';
            } elseif (($row['accuracy'] > 0 && $row['accuracy'] < 60) || ($row['accuracy'] == 0 && $dateForecast < $dateToday)) {
                $color = '#FF0000';
            }

            $events[] = [
                'title'       => $title,
                'start'       => $dateForecast,
                'color'       => $color,
                'url'         => $url,
                'description' => $description,
                'accuracy'    => (float)$row['accuracy'],
                'note'        => $row['note'],
            ];
        }

        $stmt->close();
    } else {
        echo "Errore nella preparazione della query: " . $__con->error;
    }
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
        <link rel="stylesheet" href="./assets/css/style_app.css?v=<?php echo filemtime('assets/css/style_app.css'); ?>">
        <link rel="stylesheet" href="./assets/css/style_dashboard.css?v=<?php echo filemtime('assets/css/style_dashboard.css'); ?>">
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

        <div class="cloud small"></div>
        <div class="cloud medium"></div>
        <div class="cloud large"></div>

        <div class="container pt-2">
            <div class="text-center">
                <h1 class="dash-title" style="font-family:'Raleway',sans-serif;font-weight:bold;color:#333;">
                    📊 Dashboard
                </h1>
            </div>
            <?php
                $fullName = htmlspecialchars($user['full_name']);
                if ($role === 'professor') {
                    echo "<h2 class=\"greeting text-center mt-2 mb-3\">Buongiorno prof. $fullName!</h2>";
                } else {
                    echo "<h2 class=\"greeting text-center mt-2 mb-3\">Ciao, $fullName! 😊</h2>";
                }
            ?>

            <!-- ══════ DESKTOP ══════ -->
            <div id="desktop-view">
                <div id="calendar-desktop"></div>
                <a href="api/generate_pdf.php?start_date=<?= $start_date ?>&end_date=<?= $end_date ?>"
                class="btn btn-outline-primary btn-pdf mt-3">
                    📥 Scarica PDF prossimi 7 giorni
                </a>
            </div>

            <!-- ══════ MOBILE ══════ -->
            <div id="mobile-view">
                <div class="view-toggle">
                    <button id="btn-grid" class="active" onclick="showGrid()">📅 Griglia</button>
                    <button id="btn-list" onclick="showList()">📋 Lista risultati</button>
                </div>

                <div id="mini-calendar-wrap">
                    <div id="mini-cal"></div>
                </div>

                <div id="list-wrap">
                    <div class="result-list" id="result-list-container"></div>
                </div>

                <a href="api/generate_pdf.php?start_date=<?= $start_date ?>&end_date=<?= $end_date ?>"
                class="btn btn-outline-primary btn-pdf mt-3">
                    📥 Scarica PDF prossimi 7 giorni
                </a>
            </div>

        </div>

        <script>
        const eventsData = <?= json_encode($events) ?>;

        /* ── Toggle ── */
        function showGrid() {
            document.getElementById('mini-calendar-wrap').classList.remove('hidden');
            document.getElementById('list-wrap').classList.remove('visible');
            document.getElementById('btn-grid').classList.add('active');
            document.getElementById('btn-list').classList.remove('active');
            
            // Forza FullCalendar a ricalcolare le dimensioni
            if (calendarMini) calendarMini.updateSize();
        }
        function showList() {
            document.getElementById('mini-calendar-wrap').classList.add('hidden');
            document.getElementById('list-wrap').classList.add('visible');
            document.getElementById('btn-list').classList.add('active');
            document.getElementById('btn-grid').classList.remove('active');
        }

        /* ── Costruzione lista ── */
        function buildList() {
            const container  = document.getElementById('result-list-container');
            if (!container) return;
            container.innerHTML = '';

            const today      = new Date(); today.setHours(0,0,0,0);
            const dayNames   = ['Dom','Lun','Mar','Mer','Gio','Ven','Sab'];
            const monthNames = ['Gennaio','Febbraio','Marzo','Aprile','Maggio','Giugno',
                                'Luglio','Agosto','Settembre','Ottobre','Novembre','Dicembre'];

            /* Prossimi 7 giorni senza previsione */
            const existingDates = new Set(eventsData.map(e => e.start));
            const upcoming = [];
            for (let i = 1; i <= 7; i++) {
                const d  = new Date(today);
                d.setDate(d.getDate() + i);
                const ds = d.getFullYear() + '-' +
                        String(d.getMonth()+1).padStart(2,'0') + '-' +
                        String(d.getDate()).padStart(2,'0');
                if (!existingDates.has(ds)) upcoming.push({ dateStr: ds, dateObj: d });
            }

            if (upcoming.length > 0) {
                const lbl = document.createElement('div');
                lbl.className = 'upcoming-label';
                lbl.textContent = '🔮 Prossimi giorni — tocca per inserire';
                container.appendChild(lbl);

                upcoming.forEach(({ dateStr, dateObj }) => {
                    const card = document.createElement('a');
                    card.href  = 'insert_forecast.php?date=' + dateStr;
                    card.className = 'result-card future';
                    card.innerHTML = `
                        <div class="card-date">
                            <span class="day-num">${dateObj.getDate()}</span>
                            <span class="day-name">${dayNames[dateObj.getDay()]}</span>
                        </div>
                        <div class="card-divider"></div>
                        <div class="card-body">
                            <div class="card-status">Inserisci previsione</div>
                            <div class="card-note">${monthNames[dateObj.getMonth()]} ${dateObj.getFullYear()}</div>
                        </div>
                        <div class="card-badge future-badge">+ Aggiungi</div>`;
                    container.appendChild(card);
                });
            }

            /* Risultati passati */
            const past = eventsData
                .filter(e => e.start <= new Date().toISOString().slice(0,10))
                .sort((a,b) => b.start.localeCompare(a.start));

            let currentMonth = null;
            past.forEach(ev => {
                const d = new Date(ev.start + 'T00:00:00');
                const monthKey = d.getFullYear() + '-' + d.getMonth();

                if (monthKey !== currentMonth) {
                    currentMonth = monthKey;
                    const mh = document.createElement('div');
                    mh.className = 'list-month-header';
                    mh.textContent = monthNames[d.getMonth()] + ' ' + d.getFullYear();
                    container.appendChild(mh);
                }

                let badgeClass = 'blue';
                if      (ev.color === '#008000') badgeClass = 'green';
                else if (ev.color === '#FF0000') badgeClass = 'red';
                else if (ev.color === '#FFD700') badgeClass = 'yellow';

                let statusText = ev.title;
                if      (ev.color === '#FFD700') statusText = 'In corso oggi';
                else if (ev.color === '#0000FF') statusText = 'Previsione inserita';
                else if (ev.accuracy >= 60)     statusText = 'Previsione accurata ✓';
                else if (ev.accuracy > 0)       statusText = 'Previsione non accurata';
                else                            statusText = 'In attesa di verifica';

                const statusColor = ev.color === '#FFD700' ? '#d97706'
                                : ev.color === '#0000FF' ? '#3b82f6'
                                : ev.color;

                const noteHtml = ev.note
                    ? `<div class="card-note">📝 ${ev.note.length > 40 ? ev.note.slice(0,40)+'…' : ev.note}</div>`
                    : '';

                const card = document.createElement(ev.url ? 'a' : 'div');
                if (ev.url) card.href = ev.url;
                card.className = 'result-card';
                card.innerHTML = `
                    <div class="card-date">
                        <span class="day-num">${d.getDate()}</span>
                        <span class="day-name">${dayNames[d.getDay()]}</span>
                    </div>
                    <div class="card-divider" style="background:${ev.color}44;"></div>
                    <div class="card-body">
                        <div class="card-status" style="color:${statusColor}">${statusText}</div>
                        ${noteHtml}
                    </div>
                    <div class="card-badge ${badgeClass}">${ev.title}</div>`;
                container.appendChild(card);
            });

            if (past.length === 0 && upcoming.length === 0) {
                container.innerHTML = '<p class="text-center text-muted mt-4">Nessuna previsione ancora. Inizia dalla griglia! 🌤️</p>';
            }
        }

        /* ── FullCalendar config condivisa ── */
        function makeCalendar(el, aspectRatio) {
            const today = new Date(); today.setHours(0,0,0,0);
            return new FullCalendar.Calendar(el, {
                initialView:     'dayGridMonth',
                locale:          'it',
                aspectRatio:     aspectRatio,
                dayHeaderFormat: aspectRatio < 1 ? { weekday: 'narrow' } : { weekday: 'short' },
                buttonText:      { today: 'Oggi' },
                events:          eventsData,

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
                    info.el.style.cursor = 'pointer';
                    info.el.onclick = function() {
                        const d = info.date;
                        if (d <= today) return;
                        const ds = d.getFullYear() + '-' +
                                String(d.getMonth()+1).padStart(2,'0') + '-' +
                                String(d.getDate()).padStart(2,'0');
                        const cal = info.view.calendar;
                        const evs = cal.getEvents().filter(e => e.startStr === ds);
                        if (evs.length === 0) {
                            window.location.href = 'insert_forecast.php?date=' + ds;
                        } else if (evs[0].url) {
                            window.location.href = evs[0].url;
                        }
                    };
                },
            });
        }

        /* ── Init ── */
        let calendarDesktop = null;
        let calendarMini    = null;

        function initCalendars() {
            if (window.innerWidth > 768) {
                if (!calendarDesktop) {
                    calendarDesktop = makeCalendar(document.getElementById('calendar-desktop'), 1.35);
                    calendarDesktop.render();
                }
            } else {
                if (!calendarMini) {
                    calendarMini = makeCalendar(document.getElementById('mini-cal'), 0.9);
                    calendarMini.render();
                    buildList();
                }
            }
        }

        document.addEventListener('DOMContentLoaded', initCalendars);

        // Ricalcola al resize (es. rotazione schermo o DevTools)
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(initCalendars, 200);
        });
        </script>

        <!-- Intro.js tour -->
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (localStorage.getItem('hadTour')) return;
            setTimeout(function() {
                var intro = introJs();
                intro.setOptions({
                    steps: [
                        { element: 'button[data-bs-target="#menuSidebar"]',
                        intro: "Clicca qui per aprire il menu! 📋", position: "bottom" },
                        { element: '.dropdown-menu',
                        intro: "Qui puoi vedere il tuo profilo 👤 e il livello di accuratezza 🎯.", position: "left" }
                    ],
                    tooltipClass: 'customTooltip', highlightClass: 'customHighlight',
                    showProgress: true,
                    nextLabel: 'Avanti →', prevLabel: '← Indietro', doneLabel: 'Finito!',
                });
                intro.onbeforechange(function() {
                    if (this._currentStep === 1) {
                        setTimeout(function() { $("#userDropdown").dropdown('toggle'); });
                        setTimeout(function() { intro.refresh(); });
                    }
                });
                intro.oncomplete(function() { localStorage.setItem('hadTour', true); }).start();
            }, 500);
        });
        </script>

        <!-- Nuvole dinamiche -->
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            function createCloud() {
                const c = document.createElement("div");
                c.classList.add("cloud", ["small","medium","large"][Math.floor(Math.random()*3)]);
                c.style.top = `${Math.random()*30+5}%`;
                document.body.appendChild(c);
                setTimeout(() => c.remove(), 60000);
            }
            setInterval(createCloud, 3000);
        });
        </script>

        <script src="./assets/js/main.js?v=2"></script>
    </body>
</html>
