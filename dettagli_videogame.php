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
    <title>Dettagli Videogioco</title>
    <link rel="stylesheet" href="CSS/videogame_dettagli.css">
</head>
<body>

<div class="container">
    <h1>Dettagli del Videogioco</h1>
    <div id="dettagli">Caricamento in corso...</div>
    <button onclick="toggleEditor()">Open list editor</button>

    <div id="editor" class="editor">
        <h3>Salva Attività Videogioco</h3>

        <label for="status">Status:</label>
        <select id="status">
            <option value="Playing">Playing</option>
            <option value="Complete">Complete</option>
            <option value="Planning" selected>Planning</option>
            <option value="Paused">Paused</option>
            <option value="Dropped">Dropped</option>
        </select>

        <label for="punteggio">Punteggio:</label>
        <input type="number" id="punteggio" step="0.1" min="0" max="10">

        <label for="ore_giocate">Ore giocate:</label>
        <input type="number" id="ore_giocate" min="0">

        <label for="start_date">Data inizio:</label>
        <input type="date" id="start_date">

        <label for="end_date">Data fine:</label>
        <input type="date" id="end_date">

        <label for="note">Note:</label>
        <textarea id="note"></textarea>

        <label for="rigiocato">Rigiocato (quante volte):</label>
        <input type="number" id="rigiocato" min="0">

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

    async function salvaAttivita() {
        let status = document.getElementById('status').value;
        let punteggio = document.getElementById('punteggio').value;
        let ore_giocate = document.getElementById('ore_giocate').value;
        let startDate = document.getElementById('start_date').value;
        let endDate = document.getElementById('end_date').value;
        let note = document.getElementById('note').value;
        let rigiocato = document.getElementById('rigiocato').value;
        let preferito = 0;
        if (document.getElementById('preferito').checked) {
            preferito = 1;
        }

        let params = new URLSearchParams(window.location.search);
        let guid = params.get("guid");

        let url = "ajax/attivita_videogame.php?videogioco_guid=" + (guid);
        url += "&status=" + status;
        url += "&punteggio=" + punteggio;
        url += "&ore_giocate=" + ore_giocate;
        url += "&start_date=" + startDate;
        url += "&end_date=" + endDate;
        url += "&note=" + note;
        url += "&rigiocato=" + rigiocato;
        url += "&preferito=" + preferito;

        let response = await fetch(url);
        let data = await response.json();

        if (data.status === "OK") {
            alert("Attività salvata con successo!");
            toggleEditor();
        } else {
            alert("Errore nel salvataggio: " + data.message);
        }
    }

    async function caricaDettagliVideogioco(guid) {
        let url = "ajax/videogame/getVideogamebyID.php?guid=" + guid;
        let response = await fetch(url);
        if (!response.ok) {
            throw new Error("Errore nella fetch.");
        }

        let txt = await response.text();
        let datiRicevuti = JSON.parse(txt);

        if (datiRicevuti["status"] === "ERR") {
            alert(datiRicevuti["msg"]);
            return null;
        } else {
            return datiRicevuti["dato"];
        }
    }

    document.addEventListener("DOMContentLoaded", async function () {
        let params = new URLSearchParams(window.location.search);
        let guid = params.get("guid");

        if (!guid) {
            document.getElementById("dettagli").innerHTML = "<div class='errore'>GUID mancante nell'URL.</div>";
            return;
        }

        let gioco = await caricaDettagliVideogioco(guid);
        if (gioco != null) {
            let html = "";

            if (gioco.immagine != "N/D") {
                html += "<div class='info'><span class='etichetta'>Immagine:</span><br><img src='" + gioco.immagine + "' alt='Immagine del gioco' class='immagine'></div>";
            } else {
                html += "<div class='info'><span class='etichetta'>Immagine:</span> Non disponibile</div>";
            }

            html += "<div class='info'><span class='etichetta'>Nome:</span> " + gioco.nome + "</div>";
            html += "<div class='info'><span class='etichetta'>Descrizione:</span> " + gioco.descrizione + "</div>";

            function creaLista(titolo, array) {
                let risultato = "<div class='info'><span class='etichetta'>" + titolo + ":</span> ";
                if (array && array.length > 0) {
                    risultato += "<ul>";
                    for (let i = 0; i < array.length; i++) {
                        risultato += "<li>" + array[i] + "</li>";
                    }
                    risultato += "</ul>";
                } else {
                    risultato += "Nessuno";
                }
                risultato += "</div>";
                return risultato;
            }

            function creaListaOggettiConImmagine(titolo, array) {
                let risultato = "<div class='info'><span class='etichetta'>" + titolo + ":</span><br>";
                if (array && array.length > 0) {
                    risultato += "<ul>";
                    for (let i = 0; i < array.length; i++) {
                        let imgSrc = "";
                        if (array[i].immagine) {
                            imgSrc = array[i].immagine;
                        }
                        let nome = "";
                        if (array[i].nome) {
                            nome = array[i].nome;
                        }
                        risultato += "<li><img src='" + imgSrc + "' alt='" + nome + "' style='height:40px; vertical-align:middle; margin-right:10px;'> " + nome + "</li>";
                    }
                    risultato += "</ul>";
                } else {
                    risultato += "Nessuno";
                }
                risultato += "</div>";
                return risultato;
            }

            html += creaLista("Generi", gioco.generi);
            html += creaLista("Piattaforme", gioco.piattaforme);
            if (gioco.data_uscita) {
                html += "<div class='info'><span class='etichetta'>Data di uscita:</span> " + gioco.data_uscita + "</div>";
            } else {
                html += "<div class='info'><span class='etichetta'>Data di uscita:</span> N/D</div>";
            }

            html += creaLista("DLC", gioco.dlc);
            html += creaLista("Developer", gioco.sviluppatori);
            html += creaLista("Publisher", gioco.publisher);
            html += creaLista("Temi", gioco.temi);
            html += creaLista("Franchise", gioco.franchises);

            let aliasesArray = [];
            if (gioco.aliases) {
                aliasesArray = gioco.aliases.split("\n");
            }

            html += creaLista("Alias", aliasesArray);

            document.getElementById("dettagli").innerHTML = html;

            // Aggiunta bottone per visualizzare dettagli estesi
            let bottone = document.createElement("button");
            bottone.textContent = "Visualizza dettagli estesi";
            bottone.className = "bottone-dettagli";
            bottone.onclick = function () {
                window.location.href = "visualizza_dettagli_videogioco.php?guid=" + guid;
            };

            document.getElementById("dettagli").appendChild(bottone);
        }
    });
</script>

</body>
</html>