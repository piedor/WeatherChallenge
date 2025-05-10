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
        <link rel="stylesheet" href="./assets/css/style_app.css">
        <link rel="stylesheet" href="./assets/css/style_video.css">
        <style>
            .ranking-container {
                max-width: 800px;
                margin: 0 auto;
                padding: 2rem;
                background-color: white;
                border-radius: 20px;
                box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            }

            h1.title {
                text-align: center;
                font-size: 2rem;
                margin-bottom: 1rem;
                animation: fadeInDown 1s ease-out;
            }

            .user-rank {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 1rem;
                margin-bottom: 1rem;
                border-radius: 12px;
                background-color: #f7f9fc;
                transition: transform 0.3s ease;
            }

            .user-rank:hover {
                transform: scale(1.02);
            }

            .rank-badge {
                font-weight: bold;
                width: 2rem;
            }

            .user-info {
                flex: 1;
                padding: 0 1rem;
            }

            .progress {
                height: 12px;
                border-radius: 10px;
                overflow: hidden;
            }

            .progress-bar {
                height: 100%;
            }

            @keyframes fadeInDown {
                from { opacity: 0; transform: translateY(-20px); }
                to { opacity: 1; transform: translateY(0); }
            }
        </style>
    </head>
    <body>
        <?php require ('./utils/menu.php'); ?>
        <div class="ranking-container">
            <h1 class="title">🏆 Classifica Globale Weather Challenge</h1>
            <div id="ranking-list">
                <p class="text-center text-muted">Caricamento classifica...</p>
            </div>
        </div>

        <script>
            async function loadGlobalRanking() {
                const response = await fetch('get_global_ranking.php');
                const users = await response.json();
                const container = document.getElementById('ranking-list');

                container.innerHTML = '';

                users.sort((a, b) => b.score - a.score);

                users.forEach((user, index) => {
                const color = user.score >= 80 ? 'green' : user.score >= 60 ? 'orange' : 'red';
                const medal = index === 0 ? '🥇' : index === 1 ? '🥈' : index === 2 ? '🥉' : `${index + 1}°`;

                const userHTML = `
                    <div class="user-rank">
                    <div class="rank-badge">${medal}</div>
                    <div class="user-info">
                        <strong>${user.full_name}</strong><br>
                        <div class="progress">
                        <div class="progress-bar" style="width: ${user.score}%; background-color: ${color};"></div>
                        </div>
                    </div>
                    <div><strong>${user.score.toFixed(2)}/100</strong></div>
                    </div>
                `;

                container.innerHTML += userHTML;
                });
            }

            loadGlobalRanking();
            setInterval(loadGlobalRanking, 60000);
        </script>
        <script src="./assets/js/main.js"></script>
    </body>
</html>
