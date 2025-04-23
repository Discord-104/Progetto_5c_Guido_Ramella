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
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f8f8;
            padding: 20px;
        }

        .container {
            background: white;
            max-width: 900px;
            margin: auto;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px #ccc;
        }

        img {
            max-width: 100%;
            border-radius: 10px;
        }

        h2, h3 {
            margin-top: 30px;
        }

        .griglia {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .griglia div {
            flex: 1 1 45%;
        }

        .personaggio, .relazione {
            margin-bottom: 15px;
        }

        .personaggio img {
            max-width: 80px;
            display: block;
            margin-bottom: 5px;
        }

        iframe {
            width: 100%;
            height: 400px;
            margin-top: 20px;
        }
    </style>
    <script>
        async function caricaDettagliAnime(id) {
            let url = "ajax/getAnimebyID.php?id=" + id;

            let response = await fetch(url);
            if (!response.ok) {
                throw new Error("Errore nella fetch!");
            }

            let txt = await response.text();
            console.log(txt);
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
            html += '<p><strong>Stagione:</strong> ' + anime.stagione + '</p>';
            html += '<p><strong>Data inizio:</strong> ' + anime.inizio + '</p>';
            html += '<p><strong>Data fine:</strong> ' + anime.fine + '</p>';
            html += '<p><strong>Studio:</strong> ' + anime.studio + '</p>';

            html += '<h3>Personaggi principali</h3>';
            html += '<div class="griglia">';
            for (let i = 0; i < anime.personaggi.length; i++) {
                let p = anime.personaggi[i];
                html += '<div class="personaggio">';
                html += '<img src="' + p.immagine + '" alt="' + p.nome + '">';
                html += '<p>' + p.nome + '</p>';
                html += '</div>';
            }
            html += '</div>';

            html += '<h3>Staff</h3>';
            html += '<ul>';
            for (let i = 0; i < anime.staff.length; i++) {
                let s = anime.staff[i];
                html += '<li>' + s.nome + ' (' + s.ruolo + ')</li>';
            }
            html += '</ul>';

            html += '<h3>Relazioni</h3>';
            html += '<ul>';
            for (let i = 0; i < anime.relazioni.length; i++) {
                let r = anime.relazioni[i];
                html += '<li>' + r.relazione + ' - ' + r.tipo + ': <a href="dettagli_anime.php?id=' + r.id + '">' + r.titolo + '</a></li>';
            }
            html += '</ul>';

            html += '<h3>Raccomandazioni</h3>';
            html += '<ul>';
            for (let i = 0; i < anime.raccomandazioni.length; i++) {
                let rec = anime.raccomandazioni[i];
                html += '<li><a href="dettagli_anime.php?id=' + rec.id + '">' + rec.titolo + '</a></li>';
            }
            html += '</ul>';

            document.getElementById("contenuto").innerHTML = html;
        }

        document.addEventListener("DOMContentLoaded", async function () {
            await caricaDettagliAnime(<?= $id ?>);
        });
    </script>
</head>
<body>
    <div class="container">
        <div id="contenuto">
            <p>Caricamento in corso...</p>
        </div>
    </div>
</body>
</html>
