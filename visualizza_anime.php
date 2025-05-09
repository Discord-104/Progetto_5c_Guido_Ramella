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
    <link rel="stylesheet" href="CSS/stile_attivita.css">
    <title>Attività Anime</title>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            caricaAttivita();

            // Ordina quando si cambia il valore nel select
            document.getElementById("ordinamento-select").addEventListener("change", function () {
                caricaAttivita();
            });
        });

        async function caricaAttivita() {
            let contenitore = document.getElementById("sezione_attivita");
            contenitore.innerHTML = "";

            let response = await fetch("ajax/carica_attivita.php");
            if (!response.ok) {
                console.error("Errore nella fetch delle attività globali");
                return;
            }

            let txt = await response.text();
            let datiRicevuti = JSON.parse(txt);

            if (datiRicevuti["status"] === "ERR") {
                console.error(datiRicevuti["msg"]);
                return;
            }

            let attività = datiRicevuti["data"];

            const ordineStatus = ["Watching", "Complete", "Paused", "Dropped", "Planning"];
            let attivitàPerStatus = {};
            for (let status of ordineStatus) {
                attivitàPerStatus[status] = [];
            }

            for (let item of attività) {
                if ("episodi_visti" in item && ordineStatus.includes(item.status)) {
                    attivitàPerStatus[item.status].push(item);
                }
            }

            let ordinamento = document.getElementById("ordinamento-select").value;

            function ordinaAttività(a, b) {
                if (ordinamento === "nome") {
                    return a.titolo.localeCompare(b.titolo);
                } else if (ordinamento === "punteggio") {
                    return b.punteggio - a.punteggio;
                } else if (ordinamento === "anno") {
                    return b.anno_uscita - a.anno_uscita;
                }
            }

            for (let status of ordineStatus) {
                if (attivitàPerStatus[status].length > 0) {
                    attivitàPerStatus[status].sort(ordinaAttività);

                    let intestazione = document.createElement("h2");
                    intestazione.textContent = status;
                    contenitore.appendChild(intestazione);

                    for (let attivitàItem of attivitàPerStatus[status]) {
                        let riga = document.createElement("div");
                        riga.className = "attivita-item";

                        let username = attivitàItem["username"];
                        let titolo = attivitàItem["titolo"];
                        let riferimento_api = attivitàItem["riferimento_api"];
                        let episodi = attivitàItem["episodi_visti"];
                        let anno = attivitàItem["anno_uscita"];
                        let formato = attivitàItem["formato"];
                        let immagine = attivitàItem["immagine"];

                        let descrizione = "<strong>" + titolo + "</strong><br>Episodi visti: " + episodi +
                            "<br>Anno: " + anno + " - Formato: " + formato;

                        if (immagine) {
                            if (attivitàItem.note && attivitàItem.note.trim() !== "") {
                                descrizione += "<br>" +
                                    "<div class='tooltip-container'>" +
                                    "<img src='" + immagine + "' alt='" + titolo + "' style='width:100px;'>" +
                                    "<div class='tooltip-text'>" + attivitàItem.note.replaceAll("\n", "<br>") + "</div>" +
                                    "</div>";
                            } else {
                                descrizione += "<br><img src='" + immagine + "' alt='" + titolo + "' style='width:100px;'>";
                            }
                        }

                        riga.innerHTML = descrizione;
                        let bottoneModifica = document.createElement("button");
                        bottoneModifica.textContent = "Modifica";
                        bottoneModifica.onclick = function () {
                            toggleEditor(attivitàItem);
                        };
                        riga.appendChild(bottoneModifica);
                        contenitore.appendChild(riga);
                    }
                }
            }
        }

        // Funzione per il pannello di editor
        function toggleEditor(dati) {
            let editor = document.getElementById('editor');

            if (dati != undefined) {
                // Popola i campi
                if (dati.status != null) {
                    document.getElementById('status').value = dati.status;
                } else {
                    document.getElementById('status').value = "Planning";
                }

                if (dati.punteggio != null) {
                    document.getElementById('punteggio').value = dati.punteggio;
                } else {
                    document.getElementById('punteggio').value = "";
                }

                if (dati.episodi_visti != null) {
                    document.getElementById('episodi_visti').value = dati.episodi_visti;
                } else {
                    document.getElementById('episodi_visti').value = "";
                }

                if (dati.data_inizio != null) {
                    document.getElementById('start_date').value = dati.data_inizio;
                } else {
                    document.getElementById('start_date').value = "";
                }

                if (dati.data_fine != null) {
                    document.getElementById('end_date').value = dati.data_fine;
                } else {
                    document.getElementById('end_date').value = "";
                }

                if (dati.note != null) {
                    document.getElementById('note').value = dati.note;
                } else {
                    document.getElementById('note').value = "";
                }

                if (dati.rewatch != null) {
                    document.getElementById('rewatch').value = dati.rewatch;
                } else {
                    document.getElementById('rewatch').value = "0";
                }

                if (dati.preferito == 1) {
                    document.getElementById('preferito').checked = true;
                } else {
                    document.getElementById('preferito').checked = false;
                }

                if (dati.riferimento_api != null) {
                    document.querySelector('#contenuto').dataset.riferimentoApi = dati.riferimento_api;
                }

                if (dati.titolo != null) {
                    document.querySelector('#editor h3').textContent = "Modifica attività per: " + dati.titolo;
                }

                // Sposta l'editor accanto all’attività
                let attivitàItem = event.target.parentElement;
                attivitàItem.appendChild(editor);
                editor.style.display = 'block';
            }
        }


        // Funzione per salvare l'attività
        async function salvaAttivita() {
            let status = document.getElementById('status').value;
            let punteggio = document.getElementById('punteggio').value;
            let episodiVisti = document.getElementById('episodi_visti').value;
            let startDate = document.getElementById('start_date').value;
            let endDate = document.getElementById('end_date').value;
            let note = document.getElementById('note').value;
            let rewatch = document.getElementById('rewatch').value;
            let preferito = 0;
            if (document.getElementById('preferito').checked) {
                preferito = 1;
            }

            let riferimento_api = document.querySelector('#contenuto').dataset.riferimentoApi;

            let url = "ajax/attivita_anime.php?";
            url += "&anime_id=" + riferimento_api;
            url += "&status=" + status;
            url += "&punteggio=" + punteggio;
            url += "&episodi_visti=" + episodiVisti;
            url += "&start_date=" + startDate;
            url += "&end_date=" + endDate;
            url += "&note=" + note;
            url += "&rewatch=" + rewatch;
            url += "&preferito=" + preferito;

            let response = await fetch(url);
            let data = await response.json();

            if (data.status == "OK") {
                alert("Attività salvata con successo!");
                toggleEditor();
                caricaAttivita(); // Ricarica le attività dopo il salvataggio
            } else {
                alert("Errore nel salvataggio dell'attività: " + data.message);
            }
        }

        // Carica automaticamente le attività e i dettagli dell'anime al caricamento della pagina
        document.addEventListener("DOMContentLoaded", function() {
            caricaAttivita();
        });
    </script>
</head>
<body>
    <div class="container">
        <div id="contenuto">
            <p>Caricamento in corso...</p>
        </div>

        <!-- Aggiunta della sezione attività mancante -->
        <div id="sezione_attivita">
            <!-- Qui verranno caricate le attività -->
        </div>

        <div id="ordinamento" style="margin: 20px 0;">
            <label for="ordinamento-select">Ordina per:</label>
            <select id="ordinamento-select">
                <option value="nome">Nome</option>
                <option value="punteggio">Punteggio</option>
                <option value="anno">Anno di uscita</option>
            </select>
        </div>

        <div id="editor" class="editor" style="display: none;">
            <h3>Salva Attività Anime</h3>

            <label for="status">Status:</label>
            <select id="status">
                <option value="Watching">Watching</option>
                <option value="Complete">Complete</option>
                <option value="Planning" selected>Planning</option>
            </select>

            <label for="punteggio">Punteggio:</label>
            <input type="number" id="punteggio" step="0.1" min="0" max="10">

            <label for="episodi_visti">Episodi Visti:</label>
            <input type="number" id="episodi_visti" min="0">

            <label for="start_date">Data inizio:</label>
            <input type="date" id="start_date">

            <label for="end_date">Data fine:</label>
            <input type="date" id="end_date">

            <label for="note">Note:</label>
            <textarea id="note"></textarea>

            <label for="rewatch">Rewatch (quante volte):</label>
            <input type="number" id="rewatch" min="0">

            <label for="preferito">Preferito:</label>
            <input type="checkbox" id="preferito">

            <button onclick="salvaAttivita()">Salva</button>
        </div>
    </div>
</body>
</html>