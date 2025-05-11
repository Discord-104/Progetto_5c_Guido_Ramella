<?php
    session_start();

    if (!isset($_SESSION["utente_id"])) {
        header("Location: login.php");
        exit;
    }
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pannello Personale</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="CSS/pannello.css">
</head>
<body>
    <!-- Sfondo parallax -->
    <div class="parallax-bg"></div>

    <div class="container">
        <div class="row mb-4">
            <div class="col-md-10">
                <h1 class="brand animated fade-in">Il Mio Pannello</h1>
            </div>
            <div class="col-md-2 text-end">
                <!-- Menu dropdown Bootstrap -->
                <div class="dropdown">
                    <button class="btn btn-outline-light border-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#5f85db" stroke-width="2">
                            <circle cx="12" cy="5" r="1"></circle>
                            <circle cx="12" cy="12" r="1"></circle>
                            <circle cx="12" cy="19" r="1"></circle>
                        </svg>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                        <li><a class="dropdown-item" href="modifica_profilo.php">Modifica Profilo</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Profilo -->
        <div class="row animated slide-in" style="animation-delay: 0.2s;">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 text-center mb-3 mb-md-0">
                                <img id="immagineProfilo" class="profile-img" src="" alt="Profilo">
                            </div>
                            <div class="col-md-9">
                                <div id="datiPersonali">
                                    <h2 id="nomeUtente" class="mb-2"></h2>
                                    <p id="infoBio"></p>
                                </div>
                                <div class="mt-3">
                                    <p><strong>Statistiche attività personali:</strong></p>
                                    <div id="statistiche" class="mt-2"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preferiti -->
        <div class="row mt-4 animated slide-in" style="animation-delay: 0.4s;">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h2 class="text-center mb-4">I Miei Preferiti</h2>
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <h5 class="brand">Anime:</h5>
                                <ul id="preferitiAnime" class="favorites-list"></ul>
                            </div>
                            <div class="col-md-6 mb-4">
                                <h5 class="brand">Manga:</h5>
                                <ul id="preferitiManga" class="favorites-list"></ul>
                            </div>
                            <div class="col-md-6 mb-4">
                                <h5 class="brand">Fumetti:</h5>
                                <ul id="preferitiFumetti" class="favorites-list"></ul>
                            </div>
                            <div class="col-md-6 mb-4">
                                <h5 class="brand">Videogiochi:</h5>
                                <ul id="preferitiVideogiochi" class="favorites-list"></ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grafici -->
        <div class="row mt-4 animated slide-in" style="animation-delay: 0.6s;">
            <div class="col-lg-6 mb-4">
                <div class="chart-container">
                    <h2 class="brand text-center mb-3">Grafico delle Attività</h2>
                    <canvas id="graficoAttivita"></canvas>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="chart-container">
                    <h2 class="brand text-center mb-3">Andamento Temporale</h2>
                    <canvas id="graficoAndamento"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        async function caricaProfilo() {
            let url = "ajax/carica_dati_personali.php";
            let response = await fetch(url);
            if (!response.ok) {
                alert("Errore nella fetch!");
                return;
            }

            let txt = await response.text();
            let dati = JSON.parse(txt);
            console.log(dati);

            if (dati["status"] === "ERR") {
                alert(dati["msg"]);
                return;
            }

            // Dati personali - mostra lo username senza @
            let dp = dati.data.dati_personali;
            if (dp) {
                document.getElementById("nomeUtente").textContent = dp.username;
                
                // Mostra bio se disponibile
                if (dp.bio && dp.bio.trim() !== '') {
                    document.getElementById("infoBio").textContent = dp.bio;
                } else {
                    document.getElementById("infoBio").textContent = "Nessuna bio disponibile";
                }
            }

            // Immagine del profilo
            let immagineProfilo = document.getElementById("immagineProfilo");
            if (dati.data.immagine_profilo) {
                immagineProfilo.src = dati.data.immagine_profilo;
            } else {
                immagineProfilo.src = "images/default_profile.png";
            }

            // Statistiche
            let s = dati.data;
            let statistiche = document.getElementById("statistiche");
            statistiche.innerHTML = "Episodi visti: " + s.episodi_visti + "<br>" +
                                    "Capitoli letti: " + s.capitoli_letti + "<br>" +
                                    "Volumi letti: " + s.volumi_letti + "<br>" +
                                    "Pagine lette (fumetti): " + s.pagine_lette + "<br>" +
                                    "Ore giocate: " + s.ore_giocate;

            // Preferiti con immagini
            popolaLista("preferitiAnime", s.preferiti_anime);
            popolaLista("preferitiManga", s.preferiti_manga);
            popolaLista("preferitiFumetti", s.preferiti_fumetti, true); 
            popolaLista("preferitiVideogiochi", s.preferiti_videogiochi);

            // Carica i grafici
            caricaGraficoPie(s);
            caricaGraficoTimeline(s.timeline);
        }

        function popolaLista(idLista, arrayElementi, isFumetto = false) {
            let ul = document.getElementById(idLista);
            ul.innerHTML = "";
            
            if (!arrayElementi || arrayElementi.length === 0) {
                let li = document.createElement("li");
                li.textContent = "Nessun preferito";
                ul.appendChild(li);
                return;
            }
            
            for (let i = 0; i < arrayElementi.length; i++) {
                let elem = arrayElementi[i];
                let li = document.createElement("li");

                // Crea l'immagine
                let img = document.createElement("img");
                if (elem.immagine && elem.immagine !== "") {
                    img.src = elem.immagine;
                } else {
                    img.src = "images/no_image.png"; // Immagine di fallback
                    img.alt = "Immagine non disponibile";
                }

                // Crea il testo
                let span = document.createElement("span");
                
                if (isFumetto) {
                    // Per i fumetti, mostra nome_volume e numero_fumetto se disponibili
                    if (elem.nome_volume && elem.numero_fumetto) {
                        span.textContent = elem.nome_volume + " #" + elem.numero_fumetto;
                    } else if (elem.nome_volume) {
                        span.textContent = elem.nome_volume;
                    } else if (elem.titolo) {
                        span.textContent = elem.titolo;
                    } else {
                        span.textContent = "Titolo non disponibile";
                    }
                } else {
                    // Per gli altri tipi, mostra solo il titolo
                    if (elem.titolo) {
                        span.textContent = elem.titolo;
                    } else {
                        span.textContent = "Titolo non disponibile";
                    }
                }

                li.appendChild(img);
                li.appendChild(span);
                ul.appendChild(li);
            }
        }

        // Funzione per caricare il grafico a torta
        function caricaGraficoPie(data) {
            let ctx = document.getElementById('graficoAttivita').getContext('2d');
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Episodi visti', 'Capitoli letti', 'Volumi letti', 'Pagine lette', 'Ore giocate'],
                    datasets: [{
                        label: 'Statistiche Attività',
                        data: [data.episodi_visti, data.capitoli_letti, data.volumi_letti, data.pagine_lette, data.ore_giocate],
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.7)',
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 206, 86, 0.7)',
                            'rgba(75, 192, 192, 0.7)',
                            'rgba(153, 102, 255, 0.7)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                color: '#e0e0e0'
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(tooltipItem) {
                                    return tooltipItem.label + ': ' + tooltipItem.raw;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Funzione per caricare il grafico temporale
        function caricaGraficoTimeline(timelineData) {
            if (!timelineData || timelineData.length === 0) {
                document.getElementById('graficoAndamento').innerHTML = '<h2>Andamento Temporale</h2><p>Nessun dato disponibile per il grafico temporale</p>';
                return;
            }

            // Raccoglie dati per mese
            let datiPerPeriodo = {};
            let tipi = ['anime', 'manga', 'fumetto', 'videogioco'];
            
            for (let i = 0; i < timelineData.length; i++) {
                let item = timelineData[i];
                if (item.data_inizio) {
                    let data = new Date(item.data_inizio);
                    let anno = data.getFullYear();
                    let mese = (data.getMonth() + 1).toString();
                    let meseFormattato = mese.length === 1 ? "0" + mese : mese;
                    let periodo = anno + "-" + meseFormattato;
                    
                    if (!datiPerPeriodo[periodo]) {
                        datiPerPeriodo[periodo] = {
                            anime: 0,
                            manga: 0,
                            fumetto: 0,
                            videogioco: 0
                        };
                    }
                    
                    let tipoValido = false;
                    for (let j = 0; j < tipi.length; j++) {
                        if (item.tipo === tipi[j]) {
                            tipoValido = true;
                            break;
                        }
                    }
                    
                    if (tipoValido) {
                        let conteggio = 1;
                        if (item.conteggio) {
                            conteggio = parseInt(item.conteggio);
                        }
                        datiPerPeriodo[periodo][item.tipo] += conteggio;
                    }
                }
            }
            
            // Converte in formato per Chart.js
            let periodi = Object.keys(datiPerPeriodo).sort();
            let etichette = [];
            
            for (let i = 0; i < periodi.length; i++) {
                let p = periodi[i];
                let parti = p.split('-');
                let anno = parti[0];
                let mese = parseInt(parti[1]);
                let nomiMesi = ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'];
                let nomeMese = nomiMesi[mese - 1];
                etichette.push(nomeMese + " " + anno);
            }
            
            // Crea dataset
            let datasets = [];
            let colori = [
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(153, 102, 255, 1)'
            ];
            
            for (let i = 0; i < tipi.length; i++) {
                let tipo = tipi[i];
                let label = tipo.charAt(0).toUpperCase() + tipo.slice(1);
                let dati = [];
                
                for (let j = 0; j < periodi.length; j++) {
                    let periodo = periodi[j];
                    dati.push(datiPerPeriodo[periodo][tipo]);
                }
                
                let colore = colori[i];
                let sfondo = colore.replace('1)', '0.2)');
                
                datasets.push({
                    label: label,
                    data: dati,
                    borderColor: colore,
                    backgroundColor: sfondo,
                    tension: 0.1
                });
            }
            
            // Crea il grafico
            let ctx = document.getElementById('graficoAndamento').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: etichette,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Numero di attività',
                                color: '#e0e0e0'
                            },
                            ticks: {
                                color: '#e0e0e0'
                            },
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            }
                        },
                        x: {
                            ticks: {
                                color: '#e0e0e0'
                            },
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                color: '#e0e0e0'
                            }
                        }
                    }
                }
            });
        }

        document.addEventListener("DOMContentLoaded", caricaProfilo);
    </script>
</body>
</html>