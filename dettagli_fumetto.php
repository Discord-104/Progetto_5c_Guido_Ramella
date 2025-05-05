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
    <title>Dettagli Fumetto</title>
    <link rel="stylesheet" href="CSS/fumetto_dettagli.css">
</head>
<body>

<div class="container">
    <h1>Dettagli del Fumetto</h1>
    <div id="dettagli">Caricamento in corso...</div>
    <button onclick="toggleEditor()">Open list editor</button>

    <div id="editor" class="editor">
        <h3>Salva Attività Fumetto</h3>

        <label for="status">Status:</label>
        <select id="status">
            <option value="Reading">Reading</option>
            <option value="Complete">Complete</option>
            <option value="Planning" selected>Planning</option>
            <option value="Paused">Paused</option>
            <option value="Dropped">Dropped</option>
        </select>

        <label for="punteggio">Punteggio:</label>
        <input type="number" id="punteggio" step="0.1" min="0" max="10">

        <label for="pagine_lette">Pagine lette:</label>
        <input type="number" id="pagine_lette" min="0">

        <label for="start_date">Data inizio:</label>
        <input type="date" id="start_date">

        <label for="end_date">Data fine:</label>
        <input type="date" id="end_date">

        <label for="note">Note:</label>
        <textarea id="note"></textarea>

        <label for="rilettura">Riletture (quante volte):</label>
        <input type="number" id="rilettura" min="0">

        <label for="preferito">Preferito:</label>
        <input type="checkbox" id="preferito">

        <button onclick="salvaAttivita()">Salva Attività</button>
    </div>
</div>

<script>
    function toggleEditor() {
        let editor = document.getElementById('editor');
        if (editor.style.display === 'none' || editor.style.display === '') {
            editor.style.display = 'block';
        } else {
            editor.style.display = 'none';
        }
    }

    async function caricaDettagliFumetto(id) {
        let response = await fetch("ajax/getFumettoByID.php?id=" + id);
        if (!response.ok) {
            throw new Error("Errore nella fetch.");
        }

        let txt = await response.text();
        let datiRicevuti = JSON.parse(txt);

        if (datiRicevuti["status"] === "ERR") {
            alert(datiRicevuti["msg"]);
            return null;
        } else {
            return datiRicevuti["data"];
        }
    }

    async function salvaAttivita() {
        let status = document.getElementById('status').value;
        let punteggio = document.getElementById('punteggio').value;
        let pagine_lette = document.getElementById('pagine_lette').value;
        let startDate = document.getElementById('start_date').value;
        let endDate = document.getElementById('end_date').value;
        let note = document.getElementById('note').value;
        let reread = document.getElementById('rilettura').value;
        let preferito = 0;
        
        if (document.getElementById('preferito').checked) {
            preferito = 1;
        }
        
        let id = new URLSearchParams(window.location.search).get("id");

        // Fix URL construction to use proper query parameters
        let url = "ajax/attivita_fumetto.php?fumetto_id=" + id;
        url += "&status=" + status;
        url += "&punteggio=" + punteggio;
        url += "&pagine_lette=" + pagine_lette;
        url += "&start_date=" + startDate;
        url += "&end_date=" + endDate;
        url += "&note=" + note;
        url += "&reread=" + reread;
        url += "&preferito=" + preferito;

        let response = await fetch(url);
        let data = await response.json();

        if (data.status === "OK") {
            alert("Attività salvata con successo!");
            toggleEditor();
        } else {
            alert("Errore nel salvataggio dell'attività: " + data.message);
        }
    }

    document.addEventListener("DOMContentLoaded", async function () {
        let params = new URLSearchParams(window.location.search);
        let id = params.get("id");

        if (!id || isNaN(id)) {
            document.getElementById("dettagli").innerHTML = "<div class='errore'>ID mancante o non valido.</div>";
            return;
        }

        let fumetto = await caricaDettagliFumetto(id);
        if (fumetto) {
            let html = "<div id='contenuto'>";
            html += "<h2>" + fumetto.titolo + "</h2>";

            if (fumetto.immagine && fumetto.immagine !== "") {
                html += "<div class='info'><span class='etichetta'>Immagine:</span><br><img src='" + fumetto.immagine + "' alt='Copertina del fumetto' class='immagine'></div>";
            } else {
                html += "<div class='info'><span class='etichetta'>Immagine:</span> Non disponibile</div>";
            }

            html += "<div class='info'><span class='etichetta'>Titolo:</span> " + fumetto.titolo + "</div>";
            html += "<div class='info'><span class='etichetta'>Volume:</span> " + fumetto.volume + "</div>";
            html += "<div class='info'><span class='etichetta'>Numero:</span> " + fumetto.numero + "</div>";
            html += "<div class='info'><span class='etichetta'>Descrizione:</span> " + fumetto.descrizione + "</div>";
            html += "<div class='info'><span class='etichetta'>Data di pubblicazione:</span> " + fumetto.data_pubblicazione + "</div>";

            html += "<div class='info'><span class='etichetta'>Alias:</span> ";
            if (fumetto.aliases && fumetto.aliases.trim() !== "") {
                let aliasesArray = fumetto.aliases.split("\n");
                html += "<ul>";
                for (let alias of aliasesArray) {
                    html += "<li>" + alias + "</li>";
                }
                html += "</ul>";
            } else {
                html += "Nessuno";
            }
            html += "</div>";

            html += "<div class='info'><span class='etichetta'>Personaggi:</span> ";
            if (fumetto.personaggi && fumetto.personaggi.length > 0) {
                html += "<ul>";
                for (let p of fumetto.personaggi) {
                    html += "<li>";
                    if (p.immagine !== "") {
                        html += "<img src='" + p.immagine + "' alt='" + p.nome + "' style='height:40px; vertical-align:middle; margin-right:10px;'>";
                    }
                    html += p.nome + "</li>";
                }
                html += "</ul>";
            } else {
                html += "Nessuno";
            }
            html += "</div>";
            html += "</div>"; // Close the contenuto div

            document.getElementById("dettagli").innerHTML = html;
        } else {
            document.getElementById("dettagli").innerHTML = "<div class='errore'>Errore nel caricamento dei dati.</div>";
        }
    });
</script>

</body>
</html>