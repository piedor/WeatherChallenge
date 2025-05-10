// https://api.highcharts.com/highcharts/lang.accessibility

var gFito1;
var gFito2;
var gExtra = [];

function setupCharts(){
    // Impostazioni grafici generale (lingua, ecc...)
    Highcharts.setOptions({
        time: {
            timezoneOffset: -1 * 60
        },
        lang: {
            contextButtonTitle: 'Menu',
            downloadCSV: 'Scarica CSV',
            downloadJPEG: 'Scarica JPEG',
            downloadMIDI: 'Scarica MIDI',
            downloadPDF: 'Scarica PDF',
            downloadPNG: 'Scarica PNG',
            downloadSVG: 'Scarica SVG',
            downloadXLS: 'Scarica XLS',
            exitFullscreen: 'Esci da schermo intero',
            hideData: 'Nascondi dati',
            loading: 'Caricamento...',
            months: ["Gennaio", "Febbraio", "Marzo", "Aprile", "Maggio", "Giugno", "Luglio", "Agosto", "Settembre", "Ottobre", "Novembre", "Dicembre"],
            noData: 'Nessun dato',
            playAsSound: 'Riproduci come suono',
            printChart: 'Stampa grafico', 
            shortMonths: ["Gen", "Feb", "Mar", "Apr", "Mag", "Giu", "Lug", "Ago", "Set", "Ott", "Nov", "Dic"],
            viewData: 'Visualizza dati',
            viewFullscreen: 'Visualizza a schermo intero',
            weekdays: ["Domenica", "Lunedì", "Martedì", "Mercoledì", "Giovedì", "Venerdì", "Sabato"]
        }
    });
}

function setGraph(id, tipo, desc, tY, vS, tSerie, serie, colore){
    // Funzione generale per graficare una serie
    var options = {
        chart: {
            renderTo: id,
            type: tipo
        },
        title: {
            text: desc
        },
        xAxis: {
            type: 'datetime',
            accessibility: {
                rangeDescription: 'Range di una giornata.'
            },
            title: {
                text: null
            },
            labels: {
                format: '{value:%e %b}'
            }
        },
        yAxis: {
            type: 'datetime',
            title: {
                text: tY
            },
            labels: {
                formatter: function () {
                    const date = new Date(this.value);
                    const M = (date.getMinutes() < 10 ? '0' : '') + date.getMinutes();
                    const H = (date.getHours() < 10 ? '0' : '') + date.getHours();
                    return `${H}:${M}`
                }
            }
        },
        tooltip: {
            crosshairs: true,
            shared: true,
            formatter: function () {
                const dateY = new Date(this.y);
                const M = (dateY.getMinutes() < 10 ? '0' : '') + dateY.getMinutes();
                const H = (dateY.getHours() < 10 ? '0' : '') + dateY.getHours();
                
                return `${Highcharts.dateFormat('%A, %e %b %Y',
                new Date(this.x))}<br/>${this.series.name}: <b>${H}:${M}</b>`
            }
        },
        legend: {
            enabled: true
        },
        series: [{
            name: tSerie,
            data: serie,
            color: colore
        }],
        exporting: {
            buttons: {
                contextButton: {
                    menuItems: [
                        "viewFullscreen", 
                        "printChart", 
                        "separator", 
                        "downloadPNG", 
                        "downloadJPEG", 
                        "downloadPDF",
                        "downloadSVG",
                        "separator",
                        "downloadCSV",
                        "downloadXLS"
                    ]
                }
            }
        },
        credits: {
            text: 'Liceo Da Vinci Trento',
            href: 'https://liceodavincitn.it/'
        }
    };

    gExtra.push(new Highcharts.Chart(options));
}

function setGrafici(serieTemperatura, serieRangeTemp, seriePrecipitazioni, serieRUmidita, serieRugiada, seriePressioneVapore, serieLUmidita){
    // Funzione per graficare i dati per fitopatie
    var optionsG1 = {
        chart: {
            renderTo: 'gFito1'
        },
        title: {
            text: "Dati meteo per fitopatie"
        },
        xAxis: {
            type: 'datetime',
            accessibility: {
                rangeDescription: 'Range di una giornata.'
            },
            title: {
                text: null
            }
        },
        yAxis: [{
            title: {
                text: "Temperatura [C°]"
            },
            labels: {
                format: '{value}'
            }
        },
        {
            title: {
                text: "Deficit di pressione di vapore [kPa]"
            },
            labels: {
                format: '{value}'
            },
            opposite: true
        }],
        tooltip: {
            crosshairs: true,
            shared: true,
            valueSuffix: "°C"
        },
        legend: {
            enabled: true
        },
        series: [
        {
            name: "Range temperatura aria",
            color: "#ffb3b3",
            type: "arearange",
            data: serieRangeTemp,
            showInLegend: false
        },    
        {
            name: "Temperatura aria",
            color: "#ff0000",
            type: "spline",
            data: serieTemperatura
        },
        {
            name: "Punto di rugiada",
            color: "#80aaff",
            type: "spline",
            data: serieRugiada
        },
        {
            name: "Deficit di pressione di vapore",
            color: "#2eb82e",
            type: "spline",
            data: seriePressioneVapore,
            yAxis: 1,
            tooltip: {
                valueSuffix: "kPa"
            }
        }
        ],
        credits: {
            text: 'Liceo Da Vinci Trento',
            href: 'https://liceodavincitn.it/'
        }
    };

    var optionsG2 = {
        chart: {
            renderTo: 'gFito2'
        },
        title: {
            text: "Dati meteo per fitopatie"
        },
        xAxis: {
            type: 'datetime',
            accessibility: {
                rangeDescription: 'Range di una giornata.'
            },
            title: {
                text: null
            }
        },
        yAxis: [
            {
                title: {
                    text: "Precipitazioni [mm]"
                },
                labels: {
                    format: '{value}'
                }
            },
            {
                title: {
                    text: "Umidità fogliare [min]"
                },
                labels: {
                    format: '{value}'
                },
                opposite: true
            },
            {
                title: {
                    text: "Umidità relativa [%]"
                },
                labels: {
                    format: '{value}'
                },
                opposite: true
            }
        ],
        tooltip: {
            crosshairs: true,
            shared: true,
            valueSuffix: "mm"
        },
        legend: {
            enabled: true
        },
        series: [
        {
            name: "Precipitazioni",
            color: "#008ae6",
            type: "column",
            data: seriePrecipitazioni
        },      
        {
            name: "Umidità fogliare",
            color: "#009900",
            type: "column",
            data: serieLUmidita,
            yAxis: 1,
            tooltip: {
                valueSuffix: "min"
            }
        },
        {
            name: "Umidità relativa",
            color: "#800080",
            type: "spline",
            data: serieRUmidita,
            yAxis: 2,
            tooltip: {
                valueSuffix: "%"
            }
        }],
        credits: {
            text: 'Liceo Da Vinci Trento',
            href: 'https://liceodavincitn.it/'
        }
    };
    gFito1 = new Highcharts.Chart(optionsG1);
    gFito2 = new Highcharts.Chart(optionsG2);
}

function addLoadHtml(){
    // Aggiungi spinner attesa
    let loadHtml = '<div class="d-flex justify-content-center align-items-center"><span class="mt-3 fs-3"><strong>Caricamento dati...</strong></span><div class="spinner-grow ms-2" role="status"><span class="sr-only visually-hidden">Loading...</span></div></div>';
    document.getElementById("gFito1").innerHTML = loadHtml;
    document.getElementById("gFito2").innerHTML = loadHtml;
    document.getElementById("gSuns").innerHTML = loadHtml;
    document.getElementById("gSunr").innerHTML = loadHtml;
}

function resetGrafici(){
    // Cancella i grafici
    gFito1.destroy();
    gFito2.destroy();
    gExtra.map(function(g){
        g.destroy();
    });
    // Cancella array grafici extra
    while(gExtra.length) {
        gExtra.pop();
    }
    addLoadHtml();
}

function initTableData(date, dati){
    // Inserisci dati nella tabella
    var tabella = document.getElementById("tabella");
    tabella.innerHTML = "";

    var table = document.createElement('TABLE');
    table.setAttribute("class", "table table-hover table-bordered");


    var tableHead = document.createElement('THEAD');
    var tableBody = document.createElement('TBODY');
    table.appendChild(tableHead);
    table.appendChild(tableBody);

    // Crea intestazioni + 1 (data)
    var tr = document.createElement('TR');
    tr.setAttribute("class", "int1");
    tr.setAttribute("scope", "col");
    tableHead.appendChild(tr);
    var th = document.createElement('TH');
    th.appendChild(document.createTextNode("Data"));
    th.setAttribute('rowSpan', 2);
    tr.appendChild(th);
    for (var i = 0; i < dati.length; i++) {
        var th = document.createElement('TH');
        th.appendChild(document.createTextNode(dati[i]["name"]));
        if(dati[i]["aggr"]){
        th.setAttribute('colSpan', dati[i]["aggr"].length);
        }
        tr.appendChild(th);
    }

    // Crea sottotitolo dati (min, max, avg ecc...)
    var tr = document.createElement('TR');
    tableHead.appendChild(tr);
    for (var i = 0; i < dati.length; i++) {
        if(dati[i]["aggr"]){
        for (var j = 0; j < dati[i]["aggr"].length; j++) {
            var td = document.createElement('TD');
            td.appendChild(document.createTextNode(dati[i]["aggr"][j]));
            tr.appendChild(td);
        }
        }
        else{
        var td = document.createElement('TD');
        td.appendChild(document.createTextNode("value"));
        tr.appendChild(td);
        }
    }

    for (var i = 0; i < date.length; i++) {
        var tr = document.createElement('TR');
        tableBody.appendChild(tr);

        var td = document.createElement('TD');
        td.appendChild(document.createTextNode(date[i]));
        tr.appendChild(td);

        for (var j = 0; j < dati.length; j++) {
        Object.keys(dati[j]["values"]).forEach(key => {
            // Prendi dato in base alla data
            var td = document.createElement('TD');
            td.appendChild(document.createTextNode(dati[j]["values"][key][i]));
            tr.appendChild(td);
        });
        }
    }
    tabella.appendChild(table);
}