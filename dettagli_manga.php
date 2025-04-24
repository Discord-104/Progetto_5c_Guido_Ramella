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

        .relazione a, .raccomandazione a {
            text-decoration: none;
            color: #333;
        }

        .relazione a:hover, .raccomandazione a:hover {
            color: #007bff;
        }
    </style>
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
                html += '<div class="relazione">';
                html += '<a href="dettagli_manga.php?id=' + r.id + '">';
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
    </div>
</body>
</html>
