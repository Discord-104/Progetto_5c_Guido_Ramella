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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dettagli Anime</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 20px;
        }
        .container {
            background: white;
            max-width: 800px;
            margin: auto;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px #ccc;
        }
        img {
            max-width: 100%;
            border-radius: 10px;
        }
        .info h2 {
            margin-top: 0;
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
            console.log(datiRicevuti);

            if (datiRicevuti["status"] == "ERR") {
                document.getElementById("contenuto").innerHTML = "<p>Errore: " + datiRicevuti["msg"] + "</p>";
                return;
            }

            let anime = datiRicevuti["data"];
            let trailerEmbed = "";

            if (anime.trailer != null) {
                let youtubeID = new URL(anime.trailer).searchParams.get("v");
                trailerEmbed = '<iframe src="https://www.youtube.com/embed/' + youtubeID + '" frameborder="0" allowfullscreen></iframe>';
            }

            let html = "";
            html += '<img src="' + anime.immagine + '" alt="' + anime.titolo + '">';
            html += '<div class="info">';
            html += '<h2>' + anime.titolo + '</h2>';
            html += '<p><strong>Descrizione:</strong> ' + anime.descrizione + '</p>';
            html += '<p><strong>Episodi:</strong> ' + anime.episodi + '</p>';
            html += '<p><strong>Punteggio medio:</strong> ' + anime.punteggio + '</p>';
            html += '<p><strong>Generi:</strong> ' + anime.generi + '</p>';
            html += '<p><strong>Stagione:</strong> ' + anime.stagione + '</p>';
            html += '<p><strong>Data di inizio:</strong> ' + anime.inizio + '</p>';
            html += '<p><a href="' + anime.url + '" target="_blank">Vai alla pagina AniList</a></p>';
            html += trailerEmbed;
            html += '</div>';

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
