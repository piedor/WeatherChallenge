<?php
    // Include il file per il controllo della sessione
    include 'utils/check_session.php';

    // Messaggio di conferma o errore
    $message = $_GET['message'] ?? null;
    $type = $_GET['type'] ?? null; // "success" o "error"
    $start_date = date('Y-m-d', strtotime('+1 days')); // domani
    $end_date = date('Y-m-d', strtotime('+7 days'));

    // Recupero delle previsioni esistenti per l'utente
    $query = "SELECT id, date, temperature, morning_desc, afternoon_desc, accuracy, note FROM forecasts WHERE user_id = ? ORDER BY date DESC";
    $stmt = $__con->prepare($query);

    if ($stmt) {
        $stmt->bind_param("i", $user_id); // Assumi che $user_id sia definito come ID utente
        $stmt->execute();
        $result = $stmt->get_result(); // Ottieni i risultati
        $events = [];
        
        while ($row = $result->fetch_assoc()) {
            // Vedi se id forecast è nel registro segnalazione plagi
            $query = "SELECT 1 FROM plagiarism_reports WHERE forecast_id = ? LIMIT 1";
            $stmt = $__con->prepare($query);
            $stmt->bind_param("i", $row['id']);
            $stmt->execute();
            $isReported = $stmt->get_result()->num_rows > 0;

            $color = '#0000FF'; // Blu di default (solo previsione inserita)
            $title = "Inserita"; // Titolo di default
            $dateForecast = $row['date'];
            $dateToday = date('Y-m-d');
            $idForecast = $row['id'];
            $description = "";

            $url = "modify_forecast.php?id=" . $idForecast; // Previsione inserita può modificare

            // Se la previsione è per oggi, cambia il titolo in "In corso"
            if ($dateForecast === $dateToday) {
                $title = "In corso";
                $color = '#FFD700';
                $url = "";
            } elseif ($row['accuracy'] > 0 || $dateForecast < $dateToday) {
                if($isReported){
                    $title = $row['accuracy'] . "% ⚠️"; 
                }
                else{
                    $title = $row['accuracy'] . "%";
                }
                $url = "details_forecast.php?id=" . $idForecast; 
            }

            if($row['note'] !== ""){
                // Nota
                $description = "📝 Nota presente";
            }

            if ($row['accuracy'] >= 60) {
                $color = '#008000'; // Verde per previsioni accurate  
            } elseif (($row['accuracy'] > 0 && $row['accuracy'] < 60) || ($row['accuracy'] == 0 && $dateForecast < $dateToday)) {
                $color = '#FF0000'; // Rosso per previsioni non accurate
            }

            $events[] = [
                'title' => $title,
                'start' => $dateForecast,
                'color' => $color,
                'url' => $url,
                'description' => $description
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
    <link rel="stylesheet" href="./assets/css/style_app.css">
    <link rel="stylesheet" href="./assets/css/style_dashboard.css">
    <!-- Intro.js -->
    <script src="https://cdn.jsdelivr.net/npm/intro.js@7.2.0/intro.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/intro.js@7.2.0/minified/introjs.min.css" rel="stylesheet">
    <!-- JQuery.js -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <!-- Fullcalendar.js -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>

    <style>
        .introjs-relativePosition {
            position: initial !important;
        }

        @keyframes moveClouds {
            from {
                transform: translateX(-200px);
                opacity: 0.6;
            }
            to {
                transform: translateX(100vw);
                opacity: 1;
            }
        }

        .cloud {
            position: absolute;
            top: 10%;
            left: -200px;
            width: 120px;
            height: 80px;
            background: url('assets/img/cloud.png') no-repeat center;
            background-size: contain;
            opacity: 0.8;
            animation: moveClouds 30s linear infinite;
            pointer-events: none;
        }

        .cloud.small {
            width: 80px;
            height: 50px;
            top: 20%;
            animation-duration: 40s;
        }

        .cloud.medium {
            width: 150px;
            height: 100px;
            top: 30%;
            animation-duration: 50s;
        }

        .cloud.large {
            width: 200px;
            height: 130px;
            top: 15%;
            animation-duration: 60s;
        }
    </style>

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
    <div class="cloud small"></div>
    <div class="cloud medium"></div>
    <div class="cloud large"></div>
    <div class="container">

        <div class="text-center mt-3">
            <h1 style="font-family: 'Raleway', sans-serif; font-weight: bold; color: #333;">
                📊 Dashboard
            </h1>
        </div>
        <?php
            $fullName = htmlspecialchars($user['full_name']);

            if ($role === 'student') {
                echo "<h2 class=\"text-center mt-3 mb-5\">Ciao, $fullName! 😊</h2>";
            } elseif ($role === 'professor') {
                echo "<h2 class=\"text-center mt-3 mb-5\">Buongiorno prof. $fullName! </h2>";
            } else{
                echo "<h2 class=\"text-center mt-3 mb-5\">Ciao, $fullName! 😊</h2>";
            }
        ?>
        <div id="calendar"></div>

        <a href="api/generate_pdf.php?start_date=<?= $start_date ?>&end_date=<?= $end_date ?>" class="btn btn-outline-primary mt-3">
            📥 Scarica PDF prossimi 7 giorni
        </a>

    </div>

    <script>
      document.addEventListener("DOMContentLoaded", function() { 
        var hadTour = localStorage.getItem('hadTour');

        // Forza un piccolo ritardo per permettere al layout di aggiornarsi
        setTimeout(function () { 
            if(!hadTour){
                var intro = introJs();
                intro.setOptions({
                    steps: [
                        {
                            element: 'button[data-bs-target="#menuSidebar"]',
                            intro: "Clicca qui per aprire il menu! 📋",
                            position: "bottom"
                        },
                        {
                            element: '.dropdown-menu',
                            intro: "Qui puoi vedere il tuo profilo 👤 e il livello di accuratezza 🎯.",
                            position: "left"
                        }
                    ],
                    tooltipClass: 'customTooltip',
                    highlightClass: 'customHighlight',
                    showProgress: true,
                    nextLabel: 'Avanti →',
                    prevLabel: '← Indietro',
                    doneLabel: 'Finito!',
                });

                intro.onbeforechange(function(element) {
                    if (this._currentStep === 1) {
                        setTimeout(function() {
                            $("#userDropdown").dropdown('toggle');
                        });
                        setTimeout(function() {
                            intro.refresh(); 
                        });
                    }
                });
                intro.oncomplete(function() {
                    localStorage.setItem('hadTour', true);
                }).start()
            }
        }, 500); // Ritardo di 500 millisecondi per forzare un aggiornamento del layout

      });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const cloudContainer = document.body;
            
            function createCloud() {
                const cloud = document.createElement("div");
                cloud.classList.add("cloud");

                // Dimensioni casuali
                const sizes = ["small", "medium", "large"];
                cloud.classList.add(sizes[Math.floor(Math.random() * sizes.length)]);

                // Altezza casuale
                cloud.style.top = `${Math.random() * 30 + 5}%`;

                cloudContainer.appendChild(cloud);

                // Rimuovi la nuvola dopo un po'
                setTimeout(() => {
                    cloud.remove();
                }, 60000);
            }
            // Genera nuvole a intervalli casuali
            setInterval(createCloud, 3000);
        });

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

                        if (events.length === 0) {
                            // Data futura senza previsione: inserisci previsione
                            window.location.href = "insert_forecast.php?date=" + dateStr;
                        } else {
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
