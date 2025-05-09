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
    <title>Attività Videogiochi</title>
    <link rel="stylesheet" href="CSS/stile_attivita.css">
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            caricaAttivita();
            document.getElementById("ordinamento-select").addEventListener("change", caricaAttivita);
        });

        async function caricaAttivita() {
            let contenitore = document.getElementById("sezione_attivita");
            contenitore.innerHTML = "";

            let response = await fetch("ajax/carica_attivita_videogame.php");
            if (!response.ok) {
                console.error("Errore nella fetch delle attività videogiochi");
                return;
            }

            let datiRicevuti = await response.json();
            if (datiRicevuti.status === "ERR") {
                console.error(datiRicevuti.msg);
                return;
            }

            let attivita = datiRicevuti.data;
            const ordineStatus = ["Playing", "Complete", "Paused", "Dropped", "Planning"];
            let attivitaPerStatus = {};

            for (let i = 0; i < ordineStatus.length; i++) {
                let status = ordineStatus[i];
                attivitaPerStatus[status] = [];
            }

            for (let i = 0; i < attivita.length; i++) {
                let item = attivita[i];
                if ("ore_giocate" in item && ordineStatus.includes(item.status)) {
                    attivitaPerStatus[item.status].push(item);
                }
            }

            let ordinamento = document.getElementById("ordinamento-select").value;

            function ordinaAttivita(a, b) {
                if (ordinamento === "nome") {
                    return a.titolo.localeCompare(b.titolo);
                }
                if (ordinamento === "punteggio") {
                    return b.punteggio - a.punteggio;
                }
                if (ordinamento === "data") {
                    return new Date(b.data_uscita) - new Date(a.data_uscita);
                }
            }

            for (let i = 0; i < ordineStatus.length; i++) {
                let status = ordineStatus[i];
                if (attivitaPerStatus[status].length > 0) {
                    attivitaPerStatus[status].sort(ordinaAttivita);

                    let intestazione = document.createElement("h2");
                    intestazione.textContent = status;
                    contenitore.appendChild(intestazione);

                    for (let j = 0; j < attivitaPerStatus[status].length; j++) {
                        let item = attivitaPerStatus[status][j];
                        let riga = document.createElement("div");
                        riga.className = "attivita-item";

                        let descrizione = "<strong>" + item.titolo + "</strong><br>";
                        descrizione += "Ore giocate: " + item.ore_giocate + " | Rigiocato: " + item.rigiocato;
                        descrizione += "<br>Data uscita: " + item.data_uscita;

                        if (item.start_date) {
                            descrizione += "<br>Inizio: " + item.start_date;
                        }

                        if (item.end_date) {
                            descrizione += " | Fine: " + item.end_date;
                        }

                        if (item.note && item.note.trim() !== "") {
                            descrizione += "<br><div class='tooltip-container'>";
                            if (item.immagine) {
                                descrizione += "<img src='" + item.immagine + "' alt='" + item.titolo + "' style='width:100px;'>";
                            }
                            descrizione += "<div class='tooltip-text'>" + item.note.replaceAll("\n", "<br>") + "</div></div>";
                        } else {
                            if (item.immagine) {
                                descrizione += "<br><img src='" + item.immagine + "' alt='" + item.titolo + "' style='width:100px;'>";
                            }
                        }

                        riga.innerHTML = descrizione;

                        let bottone = document.createElement("button");
                        bottone.textContent = "Modifica";
                        bottone.onclick = function () {
                            toggleEditor(item);
                        };
                        riga.appendChild(bottone);
                        contenitore.appendChild(riga);
                    }
                }
            }
        }

        function toggleEditor(dati) {
            let editor = document.getElementById("editor");

            if (dati.status) {
                document.getElementById("status").value = dati.status;
            } else {
                document.getElementById("status").value = "Planning";
            }

            if (dati.punteggio) {
                document.getElementById("punteggio").value = dati.punteggio;
            } else {
                document.getElementById("punteggio").value = "";
            }

            if (dati.ore_giocate) {
                document.getElementById("ore_giocate").value = dati.ore_giocate;
            } else {
                document.getElementById("ore_giocate").value = 0;
            }

            if (dati.rigiocato) {
                document.getElementById("rigiocato").value = dati.rigiocato;
            } else {
                document.getElementById("rigiocato").value = 0;
            }

            if (dati.start_date) {
                document.getElementById("start_date").value = dati.start_date;
            } else {
                document.getElementById("start_date").value = "";
            }

            if (dati.end_date) {
                document.getElementById("end_date").value = dati.end_date;
            } else {
                document.getElementById("end_date").value = "";
            }

            if (dati.note) {
                document.getElementById("note").value = dati.note;
            } else {
                document.getElementById("note").value = "";
            }

            if (dati.preferito == 1) {
                document.getElementById("preferito").checked = true;
            } else {
                document.getElementById("preferito").checked = false;
            }

            document.querySelector('#contenuto').dataset.riferimentoApi = dati.guid;
            document.querySelector('#editor h3').textContent = "Modifica attività per: " + dati.titolo;

            let attivitaItem = event.target.parentElement;
            attivitaItem.appendChild(editor);
            editor.style.display = 'block';
        }

        async function salvaAttivita() {
            let url = "ajax/attivita_videogame.php?";
            url += "videogioco_guid=" + document.querySelector('#contenuto').dataset.riferimentoApi;
            url += "&status=" + document.getElementById("status").value;
            url += "&punteggio=" + document.getElementById("punteggio").value;
            url += "&ore_giocate=" + document.getElementById("ore_giocate").value;
            url += "&rigiocato=" + document.getElementById("rigiocato").value;
            url += "&start_date=" + document.getElementById("start_date").value;
            url += "&end_date=" + document.getElementById("end_date").value;
            url += "&note=" + document.getElementById("note").value;

            if (document.getElementById("preferito").checked) {
                url += "&preferito=1";
            } else {
                url += "&preferito=0";
            }

            let response = await fetch(url);
            let data = await response.json();

            if (data.status === "OK") {
                alert("Attività videogioco salvata con successo!");
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
        <div id="contenuto"><p>Caricamento videogiochi in corso...</p></div>

        <div id="sezione_attivita"></div>

        <div id="ordinamento" style="margin: 20px 0;">
            <label for="ordinamento-select">Ordina per:</label>
            <select id="ordinamento-select">
                <option value="nome">Nome</option>
                <option value="punteggio">Punteggio</option>
                <option value="data">Data Uscita</option>
            </select>
        </div>

        <div id="editor" class="editor" style="display: none;">
            <h3>Modifica attività videogioco</h3>

            <label for="status">Status:</label>
            <select id="status">
                <option value="Playing">Playing</option>
                <option value="Complete">Complete</option>
                <option value="Planning">Planning</option>
                <option value="Paused">Paused</option>
                <option value="Dropped">Dropped</option>
            </select>

            <label for="punteggio">Punteggio:</label>
            <input type="number" id="punteggio" step="0.1" min="0" max="10">

            <label for="ore_giocate">Ore giocate:</label>
            <input type="number" id="ore_giocate" min="0">

            <label for="rigiocato">Rigiocato:</label>
            <input type="number" id="rigiocato" min="0">

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
