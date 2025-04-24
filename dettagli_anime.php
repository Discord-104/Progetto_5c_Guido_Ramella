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

        .personaggio, .relazione, .staff-membro, .raccomandazione {
            margin-bottom: 15px;
        }

        .personaggio img, .staff-membro img, .raccomandazione img {
            max-width: 80px;
            display: block;
            margin-bottom: 5px;
        }

        iframe {
            width: 100%;
            height: 400px;
            margin-top: 20px;
        }

        .tags {
            margin: 10px 0;
            font-style: italic;
            color: #555;
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
