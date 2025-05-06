<?php
    session_start();
    if (!isset($_SESSION["utente_id"])) {
        header("Location: login.php");
        exit;
    }

    if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
        echo "<h2>Errore: parametro ID non valido.</h2>";
        exit;
    }

    $id = (int) $_GET["id"];
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Dettagli Manga</title>
    <link rel="stylesheet" href="CSS/dettagli_manga.css">
    <script>
        async function caricaDettagliManga(id) {
            let url = "ajax/getMangaByID.php?id=" + id;

            let response = await fetch(url);
            if (!response.ok) {
                throw new Error("Errore nella fetch!");
            }

            let txt = await response.text();
            let datiRicevuti = JSON.parse(txt);

            if (datiRicevuti["status"] == "ERR") {
                document.getElementById("contenuto").innerHTML = "<p>Errore: " + datiRicevuti["msg"] + "</p>";
                return;
            }

            let manga = datiRicevuti["data"];
            let html = "";

            html += '<img src="' + manga.immagine + '" alt="' + manga.titolo + '">';
            html += '<h2>' + manga.titolo + '</h2>';
            html += '<p><strong>Descrizione:</strong> ' + manga.descrizione + '</p>';
            html += '<p><strong>Capitoli:</strong> ' + manga.capitoli + '</p>';
            html += '<p><strong>Punteggio:</strong> ' + manga.punteggio + '</p>';
            html += '<p><strong>Generi:</strong> ' + manga.generi + '</p>';
            html += '<p><strong>Tag:</strong> <span class="tags">' + manga.tags.join(", ") + '</span></p>';
            html += '<p><strong>Data inizio:</strong> ' + manga.inizio + '</p>';
            html += '<p><strong>Data fine:</strong> ' + manga.fine + '</p>';

            html += '<h3>Personaggi principali</h3>';
            html += '<div class="griglia">';
            for (let p of manga.personaggi) {
                html += '<div class="personaggio">';
                html += '<img src="' + p.immagine + '" alt="' + p.nome + '">';
                html += '<p>' + p.nome + '</p>';
                html += '</div>';
            }
            html += '</div>';

            html += '<h3>Staff</h3>';
            html += '<div class="griglia">';
            for (let s of manga.staff) {
                html += '<div class="staff-membro">';
                html += '<img src="' + s.immagine + '" alt="' + s.nome + '">';
                html += '<p><strong>' + s.nome + '</strong><br><span>' + s.ruolo + '</span></p>';
                html += '</div>';
            }
            html += '</div>';

            html += '<h3>Relazioni</h3>';
            html += '<div class="griglia">';
            for (let r of manga.relazioni) {
                let paginaRelazione = "";
                if (r.tipo === "MANGA") {
                    paginaRelazione = "dettagli_manga.php";
                } else {
                    paginaRelazione = "dettagli_anime.php";
                }

                html += '<div class="relazione">';
                html += '<a href="' + paginaRelazione + '?id=' + r.id + '">';
                if (r.immagine) {
                    html += '<img src="' + r.immagine + '" alt="' + r.titolo + '" style="max-width: 100px; margin-right: 10px;">';
                }
                html += '<p>' + r.relazione + ' - ' + r.tipo + ': ' + r.titolo + '</p>';
                html += '</a>';
                html += '</div>';
            }
            html += '</div>';

            html += '<h3>Raccomandazioni</h3>';
            html += '<div class="griglia">';
            for (let rec of manga.raccomandazioni) {
                html += '<div class="raccomandazione">';
                html += '<a href="dettagli_manga.php?id=' + rec.id + '">';
                html += '<img src="' + rec.immagine + '" alt="' + rec.titolo + '">';
                html += '<p>' + rec.titolo + '</p>';
                html += '</a>';
                html += '</div>';
            }
            html += '</div>';

            if (manga.trailer) {
                html += '<h3>Trailer</h3>';
                html += '<iframe src="https://www.youtube.com/embed/' + manga.trailer.id + '" frameborder="0" allowfullscreen></iframe>';
            }

            document.getElementById("contenuto").innerHTML = html;
        }

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
        let capitoli_letti = document.getElementById('capitoli_letti').value;
        let volumi_letti = document.getElementById('volumi_letti').value; // Ottieni il valore dei volumi letti
        let startDate = document.getElementById('start_date').value;
        let endDate = document.getElementById('end_date').value;
        let note = document.getElementById('note').value;
        let reread = document.getElementById('rilettura').value;
        let preferito = 0;
        if (document.getElementById('preferito').checked) {
            preferito = 1;
        }

        let url = "ajax/attivita_manga.php?";
        url += "&manga_id=" + <?= $id ?>;
        url += "&status=" + status;
        url += "&punteggio=" + punteggio;
        url += "&capitoli_letti=" + capitoli_letti;
        url += "&volumi_letti=" + volumi_letti; // Aggiungi il parametro volumi_letti all'URL
        url += "&start_date=" + startDate;
        url += "&end_date=" + endDate;
        url += "&note=" + note;
        url += "&reread=" + reread;
        url += "&preferito=" + preferito;

        let response = await fetch(url);
        let data = await response.json();

        if (data.status == "OK") {
            alert("Attività salvata con successo!");
            toggleEditor();
        } else {
            alert("Errore nel salvataggio dell'attività: " + data.message);
        }
    }

        document.addEventListener("DOMContentLoaded", async function () {
            await caricaDettagliManga(<?= $id ?>);
        });
    </script>
</head>
<body>
    <div class="container">
        <div id="contenuto">
            <p>Caricamento in corso...</p>
        </div>

        <button onclick="toggleEditor()">Open list editor</button>

        <div id="editor" class="editor" style="display: none; margin-top: 30px;">
            <h3>Salva Attività Manga</h3>

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

            <label for="capitoli_letti">Capitoli letti:</label>
            <input type="number" id="capitoli_letti" min="0">

            <label for="volumi_letti">Volumi letti:</label>
            <input type="number" id="volumi_letti" min="0">

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
</body>
</html>

