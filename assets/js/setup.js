/**
 * Configurazione Globale Highcharts
 * Imposta la lingua italiana e le opzioni di esportazione
 */
function setupCharts() {
    Highcharts.setOptions({
        time: {
            timezoneOffset: -1 * 60 // UTC+1
        },
        lang: {
            months: ["Gennaio", "Febbraio", "Marzo", "Aprile", "Maggio", "Giugno", "Luglio", "Agosto", "Settembre", "Ottobre", "Novembre", "Dicembre"],
            shortMonths: ["Gen", "Feb", "Mar", "Apr", "Mag", "Giu", "Lug", "Ago", "Set", "Ott", "Nov", "Dic"],
            weekdays: ["Domenica", "Lunedì", "Martedì", "Mercoledì", "Giovedì", "Venerdì", "Sabato"],
            contextButtonTitle: 'Menu',
            downloadPNG: 'Scarica PNG',
            downloadJPEG: 'Scarica JPEG',
            downloadPDF: 'Scarica PDF',
            downloadSVG: 'Scarica SVG',
            printChart: 'Stampa grafico',
            viewFullscreen: 'Visualizza a schermo intero'
        }
    });
}

/**
 * Crea il grafico meteo con icone WMO per i dettagli previsione
 * @param {string} containerId - L'ID del div HTML
 * @param {Array} hours - Array delle ore ["00:00", ...]
 * @param {Array} icons - Array delle emoji ["☀️", ...]
 * @param {Array} temps - Array delle temperature [12.5, ...]
 */
function createWeatherDetailsChart(containerId, hours, icons, temps) {
    return Highcharts.chart(containerId, {
        chart: {
            type: "spline",
            backgroundColor: 'transparent',
            style: { fontFamily: 'inherit' }
        },
        title: {
            text: "Andamento Orario Reale",
            style: { fontSize: '16px', fontWeight: 'bold' }
        },
        xAxis: {
            categories: hours,
            labels: {
                useHTML: true,
                // Dirada le etichette su mobile (ogni 4 ore) rispetto a desktop (ogni ora)
                step: window.innerWidth < 768 ? 4 : 1,
                formatter: function() {
                    let idx = this.pos;
                    return `
                        <div style="text-align: center;">
                            <div style="font-size: 20px; line-height: 1;">${icons[idx]}</div>
                            <div style="font-size: 10px; color: #666; margin-top: 3px;">${this.value}</div>
                        </div>`;
                }
            }
        },
        yAxis: {
            title: { text: "Temperatura (°C)" },
            labels: { format: '{value}°' }
        },
        tooltip: {
            shared: true,
            useHTML: true,
            formatter: function() {
                let i = this.points[0].point.index;
                return `
                    <div style="padding: 5px;">
                        <b>Ora: ${hours[i]}</b><br/>
                        Meteo: <span style="font-size:1.2em">${icons[i]}</span><br/>
                        Temperatura: <b>${this.points[0].y}°C</b>
                    </div>`;
            }
        },
        series: [{
            name: "Temperatura",
            data: temps,
            color: "#ff5733",
            marker: { enabled: true, radius: 4, symbol: 'circle' }
        }],
        responsive: {
            rules: [{
                condition: { maxWidth: 500 },
                chartOptions: {
                    chart: { height: 300 },
                    yAxis: { title: { text: null } }
                }
            }]
        },
        credits: { text: 'Liceo Da Vinci Trento', href: 'https://liceodavincitn.it/' }
    });
}