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
    <link rel="stylesheet" href="CSS/pannello_personale.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

    <h1>Il Mio Pannello</h1>

    <!-- Menu a tendina spostato in alto a destra -->
    <div class="menu" onclick="toggleMenu()">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="5" r="1"></circle>
            <circle cx="12" cy="12" r="1"></circle>
            <circle cx="12" cy="19" r="1"></circle>
        </svg>
        <div class="dropdown-content">
            <a href="modifica_profilo.php">Modifica Profilo</a>
        </div>
    </div>

    <table id="profilo">
        <tr>
            <td rowspan="2">
                <img id="immagineProfilo" src="" alt="Profilo">
            </td>
            <td>
                <div id="datiPersonali">
                    <h2 id="nomeUtente"></h2>
                    <p id="infoBio"></p>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <p><strong>Statistiche attività personali:</strong></p>
                <div id="statistiche"></div>
            </td>
        </tr>
    </table>

    <table id="preferiti">
        <tr>
            <td colspan="2">
                <h2>I Miei Preferiti</h2>
            </td>
        </tr>
        <tr>
            <td>
                <p><strong>Anime:</strong></p>
                <ul id="preferitiAnime"></ul>
            </td>
            <td>
                <p><strong>Manga:</strong></p>
                <ul id="preferitiManga"></ul>
            </td>
        </tr>
        <tr>
            <td>
                <p><strong>Fumetti:</strong></p>
                <ul id="preferitiFumetti"></ul>
            </td>
            <td>
                <p><strong>Videogiochi:</strong></p>
                <ul id="preferitiVideogiochi"></ul>
            </td>
        </tr>
    </table>

    <!-- Grafici -->
    <div class="grafici-container">
        <!-- Grafico a torta per le statistiche generali -->
        <div id="grafico">
            <h2>Grafico delle Attività</h2>
            <canvas id="graficoAttivita"></canvas>
        </div>
        
        <!-- Grafico temporale per l'andamento delle attività -->
        <div id="graficoTimeline">
            <h2>Andamento Temporale</h2>
            <canvas id="graficoAndamento"></canvas>
        </div>
    </div>

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
                document.getElementById('graficoTimeline').innerHTML = '<h2>Andamento Temporale</h2><p>Nessun dato disponibile per il grafico temporale</p>';
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
                                text: 'Numero di attività'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    }
                }
            });
        }

        // Funzione per alternare la visibilità del menu
        function toggleMenu() {
            let menu = document.querySelector('.dropdown-content');
            menu.classList.toggle('show');
        }

        // Chiudi il menu quando si fa clic altrove
        window.onclick = function(event) {
            if (!event.target.matches('.menu') && !event.target.closest('.menu')) {
                let dropdowns = document.getElementsByClassName("dropdown-content");
                for (let i = 0; i < dropdowns.length; i++) {
                    let openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        }

        document.addEventListener("DOMContentLoaded", caricaProfilo);
    </script>
</body>
</html>