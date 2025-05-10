<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Previsioni Studenti</title>
    <meta name="description" content="WebApp previsioni meteo">
    <meta name="author" content="Pietro Dorighi">
    <link href="./favicon.ico" rel="shortcut icon" type="image/vnd.microsoft.icon">
    <?php require_once './utils/style.php'; ?>
    <link rel="stylesheet" href="./assets/css/style_app.css">
    <link rel="stylesheet" href="./assets/css/style_video.css">
    
    <style>
        /* Stile per il podio */
        .top-student {
            font-size: 1.2em;
            font-weight: bold;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 10px;
            animation: bounce 2s infinite;
        }

        .progress {
            height: 15px;
            border-radius: 10px;
            margin-top: 10px;
        }

        .forecast-block {
            border-radius: 10px;
            padding: 15px;
            margin: 10px;
            text-align: center;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
            opacity: 0;
            animation: fadeIn 1s forwards;
        }

        .forecast-block.empty {
            background: #f8f9fa;
            color: #6c757d;
            font-style: italic;
            opacity: 0.7;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }
        @keyframes typing {
    from { width: 0; }
    to { width: 100%; }
}

@keyframes blink {
    50% { border-color: transparent; }
}

.animated-title {
    font-size: 2em;
    font-weight: bold;
    overflow: hidden;
    white-space: nowrap;
    width: 0;
    display: inline-block;
    border-right: 3px solid orange;
    animation: typing 3s steps(30, end) forwards, blink 0.7s infinite;
}


    </style>
</head>

<body>
    <?php require ('./utils/menu.php'); ?>
    <div class="container">
        <h1 class="text-center mb-4 animated-title">Previsioni Meteo a Video</h1>
        <div id="forecast-container" class="student-group">
            <p class="text-center text-muted">Caricamento in corso...</p>
        </div>
    </div>

    <script>
        function getNextFiveDates() {
            const dates = [];
            const days = ["DOM", "LUN", "MAR", "MER", "GIO", "VEN", "SAB"];
            const today = new Date();

            for (let i = 0; i < 5; i++) {
                let futureDate = new Date();
                futureDate.setDate(today.getDate() + i);
                let formattedDate = futureDate.toLocaleDateString("it-IT");
                let dayName = days[futureDate.getDay()];
                dates.push({ date: formattedDate, day: dayName });
            }
            return dates;
        }

        async function loadForecasts() {
            const response = await fetch('get_forecasts.php');
            const forecasts = await response.json();
            const forecastContainer = document.getElementById('forecast-container');

            forecastContainer.innerHTML = '';

            if (forecasts.length > 0) {
                const groupedForecasts = groupByStudent(forecasts);
                let rank = 1;

                for (const student in groupedForecasts) {
                    const score = groupedForecasts[student][0].score;

                    const studentGroup = document.createElement('div');
                    studentGroup.classList.add('student-group');

                    const studentName = `
                        <div class="student-name ${rank <= 3 ? 'top-student' : ''}" 
                            style="background-color: ${getBackgroundColor(rank)}; padding: 10px; border-radius: 8px;">
                            ${getRankingIcon(rank)} <strong>${student}</strong> 
                            <span style="color: ${getscoreColor(score)};">
                                (Affidabilità: ${score.toFixed(2)}%)
                            </span>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" 
                                     style="width: ${score}%; background-color: ${getscoreColor(score)};" 
                                     aria-valuenow="${score}" aria-valuemin="0" aria-valuemax="100">
                                    ${score.toFixed(2)}%
                                </div>
                            </div>
                        </div>`;

                    studentGroup.innerHTML += studentName;
                    const forecastRow = document.createElement('div');
                    forecastRow.classList.add('forecast-container');

                    const allDates = getNextFiveDates();

                    allDates.forEach(({ date, day }) => {
                        const forecast = groupedForecasts[student].find(f => f.date === date);

                        if (forecast) {
                            const icon = getWeatherIcon(forecast.morning_desc);
                            forecastRow.innerHTML += `
                                <div class="forecast-block">
                                    <div class="forecast-date"><strong>${date} (${day})</strong></div>
                                    <div class="forecast-icon">${icon}</div>
                                    <div class="forecast-details">
                                        Mattina: ${forecast.morning_desc}<br>
                                        Pomeriggio: ${forecast.afternoon_desc}<br>
                                        Temp Min: ${forecast.temp_min}°C <br>
                                        Temp Max: ${forecast.temp_max}°C <br>
                                        ${forecast.note ? `<br><em style="color: #e67e22;">📝: ${forecast.note}</em>` : ''}
                                    </div>
                                </div>
                            `;
                        } else {
                            forecastRow.innerHTML += `
                                <div class="forecast-block empty">
                                    <div class="forecast-date"><strong>${date} (${day})</strong></div>
                                    <div class="forecast-icon">📭</div>
                                    <div class="forecast-details">Nessuna previsione</div>
                                </div>
                            `;
                        }
                    });

                    studentGroup.appendChild(forecastRow);
                    forecastContainer.appendChild(studentGroup);
                    rank++;
                }
            } else {
                forecastContainer.innerHTML = '<p class="text-center text-muted">Non ci sono previsioni disponibili.</p>';
            }
        }

        function groupByStudent(forecasts) {
            return forecasts.reduce((groups, forecast) => {
                const { full_name } = forecast;
                if (!groups[full_name]) {
                    groups[full_name] = [];
                }
                groups[full_name].push(forecast);
                return groups;
            }, {});
        }

        function getWeatherIcon(description) {
            if (description.toLowerCase() === 'soleggiato') return '☀️';
            if (description.toLowerCase() === 'nuvoloso') return '☁️';
            if (description.toLowerCase() === 'parzialmente nuvoloso') return '⛅';
            if (description.toLowerCase() === 'pioggia') return '🌧️';
            if (description.toLowerCase() === 'neve') return '❄️';
            if (description.toLowerCase() === 'grandine') return '⚽';
            if (description.toLowerCase() === 'temporale') return '🌩️';
            return '🌈';
        }

        function getscoreColor(score) {
            if (score >= 80) return "green";
            if (score >= 60) return "orange";
            return "red";
        }

        function getBackgroundColor(rank) {
            if (rank === 1) return "#FFD700";  
            if (rank === 2) return "#C0C0C0";  
            if (rank === 3) return "#CD7F32";  
            return "white";
        }

        function getRankingIcon(rank) {
            if (rank === 1) return "🏆";
            if (rank === 2) return "🥈";
            if (rank === 3) return "🥉";
            return rank + "° ";
        }

        setInterval(loadForecasts, 60000);
        loadForecasts();
    </script>
    <script src="./assets/js/main.js"></script>
</body>
</html>
