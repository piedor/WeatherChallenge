<?php
    include 'utils/check_session.php';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Previsioni Meteo a Video</title>
    <meta name="description" content="WebApp previsioni meteo">
    <meta name="author" content="Pietro Dorighi">
    <link href="./favicon.ico" rel="shortcut icon" type="image/vnd.microsoft.icon">
    <?php require_once './utils/style.php'; ?>
    <link rel="stylesheet" href="./assets/css/style_app.css?v=<?php echo filemtime('assets/css/style_app.css'); ?>">
    <link rel="stylesheet" href="./assets/css/style_video.css?v=<?php echo filemtime('assets/css/style_video.css'); ?>">
</head>
<body>
    <?php require('./utils/header.php'); ?>

    <div class="container py-3">
        <div class="title-wrap mb-3">
            <h1 class="video-title">Previsioni Meteo a Video</h1>
        </div>

        <div id="forecast-container"></div>
        <p class="last-update" id="last-update"></p>
    </div>

    <script>
    const DAYS = ["DOM","LUN","MAR","MER","GIO","VEN","SAB"];

    function getNextFiveDates() {
        const today = new Date();
        return Array.from({ length: 5 }, (_, i) => {
            const d = new Date();
            d.setDate(today.getDate() + i);
            return {
                date: d.toLocaleDateString('it-IT'),
                day:  DAYS[d.getDay()]
            };
        });
    }

    const weatherIcons = {
        'soleggiato':            '☀️',
        'nuvoloso':              '☁️',
        'parzialmente nuvoloso': '⛅',
        'pioggia':               '🌧️',
        'neve':                  '❄️',
        'grandine':              '⚽',
        'temporale':             '🌩️',
    };
    const getIcon = desc => weatherIcons[desc?.toLowerCase()] ?? '🌈';

    const scoreColor = s => s >= 80 ? 'var(--green)' : s >= 60 ? 'var(--orange)' : 'var(--red)';

    const medals = ['🥇','🥈','🥉'];
    const badgeClass = rank => rank === 1 ? 'gold' : rank === 2 ? 'silver' : rank === 3 ? 'bronze' : 'normal';

    function groupByStudent(forecasts) {
        return forecasts.reduce((g, f) => {
            if (!g[f.full_name]) g[f.full_name] = [];
            g[f.full_name].push(f);
            return g;
        }, {});
    }

    async function loadForecasts() {
        const response  = await fetch('get_forecasts.php');
        const forecasts = await response.json();
        const container = document.getElementById('forecast-container');
        container.innerHTML = '';

        if (!forecasts.length) {
            container.innerHTML = '<p class="text-center text-muted">Nessuna previsione disponibile.</p>';
            return;
        }

        const grouped = groupByStudent(forecasts);
        const dates   = getNextFiveDates();
        let rank = 1;

        for (const studentName in grouped) {
            const score  = grouped[studentName][0].score;
            const color  = scoreColor(score);
            const medal  = medals[rank - 1] ?? `${rank}°`;
            const bClass = badgeClass(rank);
            const delay  = (rank - 1) * 0.08;

            // Card
            const card = document.createElement('div');
            card.className = 'student-card';
            card.style.animationDelay = `${delay}s`;

            // Header
            card.innerHTML = `
                <div class="student-header">
                    <div class="student-medal">${medal}</div>
                    <div class="student-meta">
                        <div class="student-name">${studentName}</div>
                        <div class="student-score-line">
                            <div class="score-bar-wrap">
                                <div class="score-bar" style="width:${score}%; background:${color};"></div>
                            </div>
                            <span class="score-label" style="color:${color}">${score.toFixed(1)}%</span>
                        </div>
                    </div>
                </div>
            `;

            // Griglia previsioni
            const grid = document.createElement('div');
            grid.className = 'forecast-grid';

            dates.forEach(({ date, day }, i) => {
                const f    = grouped[studentName].find(x => x.date === date);
                const cell = document.createElement('div');
                cell.style.animationDelay = `${delay + i * 0.05}s`;

                if (f) {
                    cell.className = 'forecast-cell';
                    cell.innerHTML = `
                        <div class="fc-day">${day}</div>
                        <div class="fc-date">${date}</div>
                        <div class="fc-weather-row">
                            <div class="fc-period morning">
                                <span class="fc-period-label">Matt</span>
                                <span class="fc-period-icon">${getIcon(f.morning_desc)}</span>
                            </div>
                            <div class="fc-period afternoon">
                                <span class="fc-period-label">Pom</span>
                                <span class="fc-period-icon">${getIcon(f.afternoon_desc)}</span>
                            </div>
                        </div>
                        <div class="fc-temp">
                            <span class="tmax">↑${f.temp_max}°</span>
                            <span class="tmin"> ↓${f.temp_min}°</span>
                        </div>
                        ${f.note ? `<div class="fc-note">📝 ${f.note}</div>` : ''}
                    `;
                } else {
                    cell.className = 'forecast-cell empty';
                    cell.innerHTML = `
                        <div class="fc-day">${day}</div>
                        <div class="fc-date">${date}</div>
                        <div class="fc-weather-row">
                            <div class="fc-period morning">
                                <span class="fc-period-label">Matt</span>
                                <span class="fc-period-icon">📭</span>
                            </div>
                            <div class="fc-period afternoon">
                                <span class="fc-period-label">Pom</span>
                                <span class="fc-period-icon">📭</span>
                            </div>
                        </div>
                    `;
                }
                grid.appendChild(cell);
            });

            card.appendChild(grid);
            container.appendChild(card);
            rank++;
        }

        document.getElementById('last-update').textContent =
            'Aggiornato alle ' + new Date().toLocaleTimeString('it-IT');
    }

    loadForecasts();
    </script>
    <script src="./assets/js/main.js?v=2"></script>
</body>
</html>