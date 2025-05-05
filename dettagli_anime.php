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
    <title>Dettagli Anime</title>
    <link rel="stylesheet" href="CSS/dettagli_anime.css">
    <script>
        async function caricaDettagliAnime(id) {
            let url = "ajax/getAnimebyID.php?id=" + id;

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

            let anime = datiRicevuti["data"];
            let html = "";

            html += '<img src="' + anime.immagine + '" alt="' + anime.titolo + '">';
            html += '<h2>' + anime.titolo + '</h2>';
            html += '<p><strong>Descrizione:</strong> ' + anime.descrizione + '</p>';
            html += '<p><strong>Episodi:</strong> ' + anime.episodi + '</p>';
            html += '<p><strong>Punteggio:</strong> ' + anime.punteggio + '</p>';
            html += '<p><strong>Generi:</strong> ' + anime.generi + '</p>';
            html += '<p><strong>Tag:</strong> <span class="tags">' + anime.tags.join(", ") + '</span></p>';
            html += '<p><strong>Stagione:</strong> ' + anime.stagione + '</p>';
            html += '<p><strong>Data inizio:</strong> ' + anime.inizio + '</p>';
            html += '<p><strong>Data fine:</strong> ' + anime.fine + '</p>';
            html += '<p><strong>Studio:</strong> ' + anime.studio + '</p>';

            if (anime.trailer) {
                html += '<h3>Trailer</h3>';
                html += '<iframe src="' + anime.trailer + '" frameborder="0" allowfullscreen></iframe>';
            }

            html += '<h3>Personaggi principali</h3>';
            html += '<div class="griglia">';
            for (let p of anime.personaggi) {
                html += '<div class="personaggio">';
                html += '<img src="' + p.immagine + '" alt="' + p.nome + '">';
                html += '<p>' + p.nome + '</p>';
                html += '</div>';
            }
            html += '</div>';

            html += '<h3>Staff</h3>';
            html += '<div class="griglia">';
            for (let s of anime.staff) {
                html += '<div class="staff-membro">';
                html += '<img src="' + s.immagine + '" alt="' + s.nome + '">';
                html += '<p><strong>' + s.nome + '</strong><br><span>' + s.ruolo + '</span></p>';
                html += '</div>';
            }
            html += '</div>';

            html += '<h3>Relazioni</h3>';
            html += '<div class="griglia">';
            for (let r of anime.relazioni) {
                html += '<div class="relazione">';
                html += '<a href="dettagli_anime.php?id=' + r.id + '">';
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
            for (let rec of anime.raccomandazioni) {
                html += '<div class="raccomandazione">';
                html += '<a href="dettagli_anime.php?id=' + rec.id + '">';
                html += '<img src="' + rec.immagine + '" alt="' + rec.titolo + '">';
                html += '<p>' + rec.titolo + '</p>';
                html += '</a>';
                html += '</div>';
            }
            html += '</div>';

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
            let episodiVisti = document.getElementById('episodi_visti').value;
            let startDate = document.getElementById('start_date').value;
            let endDate = document.getElementById('end_date').value;
            let note = document.getElementById('note').value;
            let rewatch = document.getElementById('rewatch').value;
            let preferito = 0;
            if (document.getElementById('preferito').checked) {
                preferito = 1;
            }

            let titolo = document.querySelector('#contenuto h2').innerText;

            let url = "ajax/attivita_anime.php?";
            url += "&anime_id=" + <?php echo $id; ?>;
            url += "&titolo=" + titolo;
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
            } else {
                alert("Errore nel salvataggio dell'attività: " + data.message);
            }
        }



        document.addEventListener("DOMContentLoaded", function() {
            caricaDettagliAnime(<?php echo $id; ?>);
        });
    </script>
</head>
<body>
    <div class="container">
        <div id="contenuto">
            <p>Caricamento in corso...</p>
        </div>

        <button onclick="toggleEditor()">Open list editor</button>

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

            <button onclick="salvaAttivita()">Salva Attività</button>
        </div>
    </div>
</body>
</html>
