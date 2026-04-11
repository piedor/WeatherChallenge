<?php
    include 'utils/check_session.php';
?>
<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Classifica Globale</title>
        <meta name="description" content="WebApp previsioni meteo">
        <meta name="author" content="Pietro Dorighi">
        <link href="./favicon.ico" rel="shortcut icon" type="image/vnd.microsoft.icon">
        <?php require_once './utils/style.php'; ?>
        <link rel="stylesheet" href="./assets/css/style_app.css?v=<?php echo filemtime('assets/css/style_app.css'); ?>">
        <link rel="stylesheet" href="./assets/css/style_dashboard.css?v=<?php echo filemtime('assets/css/style_dashboard.css'); ?>">
        <link rel="stylesheet" href="./assets/css/style_ranking.css?v=<?php echo filemtime('assets/css/style_ranking.css'); ?>">
    </head>
    <body>
        <?php require('./utils/header.php'); ?>

        <div class="ranking-container">
            <h1 class="rank-title">🏆 Classifica Globale</h1>
            <p class="rank-subtitle">WeatherChallenge — aggiornata in tempo reale</p>

            <!-- Podio top 3 -->
            <div class="podium" id="podium">
                <!-- popolato da JS -->
            </div>

            <!-- Lista 4° in poi -->
            <div class="rank-list" id="ranking-list">
                <p class="text-center text-muted">Caricamento classifica...</p>
            </div>

            <p class="last-update" id="last-update"></p>
        </div>

        <script>
        async function loadGlobalRanking() {
            const response = await fetch('get_global_ranking.php');
            const users    = await response.json();

            users.sort((a, b) => b.score - a.score);

            const podium    = document.getElementById('podium');
            const list      = document.getElementById('ranking-list');
            const lastUp    = document.getElementById('last-update');

            podium.innerHTML = '';
            list.innerHTML   = '';

            const scoreColor = s => s >= 80 ? 'var(--green)' : s >= 60 ? 'var(--orange)' : 'var(--red)';

            /* ── Podio top 3 ── */
            const podiumOrder = [1, 0, 2]; // 2°, 1°, 3° per effetto podio
            const podiumClasses  = ['silver', 'gold', 'bronze'];
            const podiumHeights  = ['second', 'first', 'third'];
            const podiumMedals   = ['🥈', '🥇', '🥉'];

            podiumOrder.forEach((idx, pos) => {
                if (!users[idx]) return;
                const u = users[idx];
                const el = document.createElement('div');
                el.className = 'podium-item';
                el.innerHTML = `
                    <div class="podium-avatar ${podiumClasses[pos]}">${podiumMedals[pos]}</div>
                    <div class="podium-name">${u.full_name.split(' ')[0]}<br>${u.full_name.split(' ').slice(1).join(' ')}</div>
                    <div class="podium-score">${u.score.toFixed(1)}%</div>
                    <div class="podium-block ${podiumHeights[pos]}">${idx + 1}°</div>
                `;
                podium.appendChild(el);
            });

            /* ── Lista dal 4° ── */
            users.slice(3).forEach((user, i) => {
                const rank  = i + 4;
                const color = scoreColor(user.score);
                const el    = document.createElement('div');
                el.className = 'user-rank';
                el.style.animationDelay = `${i * 0.05}s`;
                el.innerHTML = `
                    <div class="rank-pos">${rank}°</div>
                    <div class="rank-info">
                        <strong>${user.full_name}</strong>
                        <div class="rank-bar-wrap">
                            <div class="rank-bar" style="width:${user.score}%; background:${color};"></div>
                        </div>
                    </div>
                    <div class="rank-score" style="color:${color}">${user.score.toFixed(1)}%</div>
                `;
                list.appendChild(el);
            });

            if (users.length === 0) {
                list.innerHTML = '<p class="text-center text-muted">Nessun dato disponibile.</p>';
            }

            lastUp.textContent = 'Ultimo aggiornamento: ' + new Date().toLocaleTimeString('it-IT');
        }

        loadGlobalRanking();
        </script>
        <script src="./assets/js/main.js?v=2"></script>
    </body>
</html>