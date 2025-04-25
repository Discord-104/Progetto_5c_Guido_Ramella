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
</div>

<script>
    async function fetchDati(url, campo) {
        let response = await fetch(url);
        if (!response.ok) {
            console.error("Errore nella fetch di " + campo);
            return [];
        }

        let data = await response.text();
        console.log(campo + ":", data);

        let json = JSON.parse(data);
        if (json.status === "OK") {
            return json[campo];
        } else {
            console.warn(campo + " non disponibili");
            return [];
        }
    }

    async function caricaDettagliVideogioco(guid) {
        let url = "ajax/videogame/getVideogamebyID.php?guid=" + guid;
        let response = await fetch(url);
        if (!response.ok) throw new Error("Errore nella fetch.");
        let dati = await response.text();
        let json = JSON.parse(dati);
        if (json.status === "OK") return json.dato;
        alert(json.msg);
        return null;
    }

    function creaListaOggetti(titolo, array) {
        let html = "<div class='info'><span class='etichetta'>" + titolo + ":</span><br>";
        if (array.length > 0) {
            html += "<ul>";
            for (let i = 0; i < array.length; i++) {
                html += "<li><img src='" + array[i].immagine + "' alt='" + array[i].nome + "' style='height:40px; vertical-align:middle; margin-right:10px;'> " + array[i].nome + "</li>";
            }
            html += "</ul>";
        } else {
            html += "Nessuno";
        }
        html += "</div>";
        return html;
    }

    document.addEventListener("DOMContentLoaded", async function () {
        function getQueryParam(nome) {
            let params = new URLSearchParams(window.location.search);
            return params.get(nome);
        }

        let guid = getQueryParam("guid");

        if (!guid) {
            document.getElementById("dettagli").innerHTML = "<div class='errore'>GUID mancante nell'URL.</div>";
            return;
        }

        let gioco = await caricaDettagliVideogioco(guid);
        if (!gioco) return;

        let personaggi = await fetchDati("ajax/videogame/getCharacters.php?guid=" + guid, "characters");
        let simili = await fetchDati("ajax/videogame/getSimilarGames.php?guid=" + guid, "similar_games");

        let html = "";

        // Immagine
        if (gioco.immagine != "N/D") {
            html += "<div class='info'><span class='etichetta'>Immagine:</span><br><img src='" + gioco.immagine + "' alt='Immagine del gioco' class='immagine'></div>";
        } else {
            html += "<div class='info'><span class='etichetta'>Immagine:</span> Non disponibile</div>";
        }

        html += "<div class='info'><span class='etichetta'>Nome:</span> " + gioco.nome + "</div>";
        html += "<div class='info'><span class='etichetta'>Descrizione:</span> " + gioco.descrizione + "</div>";

        html += creaListaOggetti("Personaggi", personaggi);
        html += creaListaOggetti("Giochi simili", simili);

        document.getElementById("dettagli").innerHTML = html;
    });
</script>

</body>
</html>