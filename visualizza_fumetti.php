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
    <title>Attività Fumetti</title>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            caricaAttivita();
            document.getElementById("ordinamento-select").addEventListener("change", function () {
                caricaAttivita();
            });
        });

        async function caricaAttivita() {
            let contenitore = document.getElementById("sezione_attivita");
            contenitore.innerHTML = "";

            let response = await fetch("ajax/carica_attivita_fumetto.php");
            if (!response.ok) {
                console.error("Errore nella fetch delle attività fumetti");
                return;
            }

            let datiRicevuti = await response.json();
            if (datiRicevuti["status"] === "ERR") {
                console.error(datiRicevuti["msg"]);
                return;
            }

            let attività = datiRicevuti["data"];
            const ordineStatus = ["Reading", "Complete", "Paused", "Dropped", "Planning"];
            let attivitàPerStatus = {};
            for (let status of ordineStatus) {
                attivitàPerStatus[status] = [];
            }

            for (let item of attività) {
                if ("numero_letti" in item && ordineStatus.includes(item.status)) {
                    attivitàPerStatus[item.status].push(item);
                }
            }

            let ordinamento = document.getElementById("ordinamento-select").value;
            function ordinaAttività(a, b) {
                if (ordinamento === "nome") {
                    return a.titolo.localeCompare(b.titolo);
                }
                if (ordinamento === "punteggio") {
                    return b.punteggio - a.punteggio;
                }
                if (ordinamento === "anno") {
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

                        let titoloVisualizzato = attivitàItem.titolo;
                        if (titoloVisualizzato === "Titolo non disponibile" || !titoloVisualizzato) {
                            titoloVisualizzato = attivitàItem.nome_volume;
                        }
                        
                        let descrizione = "<strong>" + titoloVisualizzato + "</strong><br>" +
                            "Pagine lette: " + attivitàItem.numero_letti + 
                            " | Riletture: " + attivitàItem.riletture +
                            "<br>Anno: " + attivitàItem.anno_uscita + 
                            " - Volume: " + attivitàItem.nome_volume;
                            
                        if (attivitàItem.numero_fumetto) {
                            descrizione += " - Numero serie: " + attivitàItem.numero_fumetto;
                        }

                        if (attivitàItem.immagine) {
                            if (attivitàItem.note && attivitàItem.note.trim() !== "") {
                                descrizione += "<br>" +
                                    "<div class='tooltip-container'>" +
                                    "<img src='" + attivitàItem.immagine + "' alt='" + attivitàItem.titolo + "' style='width:100px;'>" +
                                    "<div class='tooltip-text'>" + attivitàItem.note.replaceAll("\n", "<br>") + "</div>" +
                                    "</div>";
                            } else {
                                descrizione += "<br><img src='" + attivitàItem.immagine + "' alt='" + attivitàItem.titolo + "' style='width:100px;'>";
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

        function toggleEditor(dati) {
            let editor = document.getElementById('editor');
            if (dati) {
                if (dati.status) {
                    document.getElementById('status').value = dati.status;
                } else {
                    document.getElementById('status').value = "Planning";
                }

                if (dati.punteggio) {
                    document.getElementById('punteggio').value = dati.punteggio;
                } else {
                    document.getElementById('punteggio').value = "";
                }

                if (dati.numero_letti) {
                    document.getElementById('numero_letti').value = dati.numero_letti;
                } else {
                    document.getElementById('numero_letti').value = "";
                }

                if (dati.riletture) {
                    document.getElementById('riletture').value = dati.riletture;
                } else {
                    document.getElementById('riletture').value = "0";
                }

                if (dati.data_inizio) {
                    document.getElementById('start_date').value = dati.data_inizio;
                } else {
                    document.getElementById('start_date').value = "";
                }

                if (dati.data_fine) {
                    document.getElementById('end_date').value = dati.data_fine;
                } else {
                    document.getElementById('end_date').value = "";
                }

                if (dati.note) {
                    document.getElementById('note').value = dati.note;
                } else {
                    document.getElementById('note').value = "";
                }

                if (dati.preferito == 1) {
                    document.getElementById('preferito').checked = true;
                } else {
                    document.getElementById('preferito').checked = false;
                }

                document.querySelector('#contenuto').dataset.riferimentoApi = dati.riferimento_api;
                
                // Use nome_volume if titolo is not available
                let displayTitle = dati.titolo;
                if (displayTitle === "Titolo non disponibile" || !displayTitle) {
                    displayTitle = dati.nome_volume;
                }
                document.querySelector('#editor h3').textContent = "Modifica attività per: " + displayTitle;

                let attivitàItem = event.target.parentElement;
                attivitàItem.appendChild(editor);
                editor.style.display = 'block';
            }
        }

        async function salvaAttivita() {
            let url = "ajax/attivita_fumetto.php?";
            url += "&fumetto_id=" + document.querySelector('#contenuto').dataset.riferimentoApi;
            url += "&status=" + document.getElementById('status').value;
            url += "&punteggio=" + document.getElementById('punteggio').value;
            url += "&pagine_lette=" + document.getElementById('numero_letti').value;
            url += "&reread=" + document.getElementById('riletture').value;
            url += "&start_date=" + document.getElementById('start_date').value;
            url += "&end_date=" + document.getElementById('end_date').value;
            url += "&note=" + document.getElementById('note').value;

            let preferito = document.getElementById('preferito').checked;
            if (preferito) {
                url += "&preferito=1";
            } else {
                url += "&preferito=0";
            }

            let response = await fetch(url);
            let data = await response.json();

            if (data.status == "OK") {
                alert("Attività fumetto salvata con successo!");
                toggleEditor();
                caricaAttivita();
            } else {
                alert("Errore nel salvataggio: " + data.message);
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <div id="contenuto"><p>Caricamento in corso...</p></div>

        <div id="sezione_attivita"></div>

        <div id="ordinamento" style="margin: 20px 0;">
            <label for="ordinamento-select">Ordina per:</label>
            <select id="ordinamento-select">
                <option value="nome">Nome</option>
                <option value="punteggio">Punteggio</option>
                <option value="anno">Anno</option>
            </select>
        </div>

        <div id="editor" class="editor" style="display: none;">
            <h3>Salva Attività Fumetto</h3>

            <label for="status">Status:</label>
            <select id="status">
                <option value="Reading">Reading</option>
                <option value="Complete">Complete</option>
                <option value="Planning">Planning</option>
                <option value="Paused">Paused</option>
                <option value="Dropped">Dropped</option>
            </select>

            <label for="punteggio">Punteggio:</label>
            <input type="number" id="punteggio" step="0.1" min="0" max="10">

            <label for="numero_letti">Pagine lette:</label>
            <input type="number" id="numero_letti" min="0">

            <label for="riletture">Riletture:</label>
            <input type="number" id="riletture" min="0">

            <label for="start_date">Data inizio:</label>
            <input type="date" id="start_date">

            <label for="end_date">Data fine:</label>
            <input type="date" id="end_date">

            <label for="note">Note:</label>
            <textarea id="note"></textarea>

            <label for="preferito">Preferito:</label>
            <input type="checkbox" id="preferito">

            <button onclick="salvaAttivita()">Salva</button>
        </div>
    </div>
</body>
</html>