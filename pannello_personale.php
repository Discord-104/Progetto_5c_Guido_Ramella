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
    <title>Pannello Personale</title>
    <link rel="stylesheet" href="CSS/pannello_personale.css">
</head>
<body>

    <h1>Il Mio Pannello</h1>

    <table id="profilo">
        <tr>
            <td rowspan="2">
                <img id="immagineProfilo" src="" alt="Profilo">
            </td>
            <td>
                <p><strong>Statistiche attività personali:</strong></p>
                <div id="statistiche"></div>
            </td>
        </tr>
    </table>

    <table id="preferiti">
        <tr>
            <td colspan="2">
                <h2>I Miei Preferiti</h2>
            </td>
        </tr>
        <tr>
            <td>
                <p><strong>Anime:</strong></p>
                <ul id="preferitiAnime"></ul>
            </td>
            <td>
                <p><strong>Manga:</strong></p>
                <ul id="preferitiManga"></ul>
            </td>
        </tr>
        <tr>
            <td>
                <p><strong>Fumetti:</strong></p>
                <ul id="preferitiFumetti"></ul>
            </td>
            <td>
                <p><strong>Videogiochi:</strong></p>
                <ul id="preferitiVideogiochi"></ul>
            </td>
        </tr>
    </table>

    <script>
        async function caricaProfilo() {
            let url = "ajax/carica_dati_personali.php";  // Assicurati che il percorso sia corretto
            let response = await fetch(url);
            if (!response.ok) {
                alert("Errore nella fetch!");
                return;
            }

            let txt = await response.text();
            let dati = JSON.parse(txt);
            console.log(dati);

            if (dati["status"] === "ERR") {
                alert(dati["msg"]);
                return;
            }

            // Immagine del profilo
            let immagineProfilo = document.getElementById("immagineProfilo");
            immagineProfilo.src = dati.data.immagine_profilo;

            // Statistiche
            let s = dati.data;
            let statistiche = document.getElementById("statistiche");
            statistiche.innerHTML = "Episodi visti: " + s.episodi_visti + "<br>" +
                                    "Capitoli letti: " + s.capitoli_letti + "<br>" +
                                    "Volumi letti: " + s.volumi_letti + "<br>" +
                                    "Pagine lette (fumetti): " + s.pagine_lette + "<br>" +
                                    "Ore giocate: " + s.ore_giocate;

            // Preferiti con immagini
            popolaLista("preferitiAnime", s.preferiti_anime);
            popolaLista("preferitiManga", s.preferiti_manga);
            popolaLista("preferitiFumetti", s.preferiti_fumetti, true); // Passando true per indicare che è un fumetto
            popolaLista("preferitiVideogiochi", s.preferiti_videogiochi);
        }

        function popolaLista(idLista, arrayElementi, isFumetto = false) {
            let ul = document.getElementById(idLista);
            ul.innerHTML = "";
            
            if (!arrayElementi || arrayElementi.length === 0) {
                let li = document.createElement("li");
                li.textContent = "Nessun preferito";
                ul.appendChild(li);
                return;
            }
            
            for (let i = 0; i < arrayElementi.length; i++) {
                let elem = arrayElementi[i];
                let li = document.createElement("li");

                // Crea l'immagine
                let img = document.createElement("img");
                if (elem.immagine && elem.immagine !== "") {
                    img.src = elem.immagine;
                } else {
                    img.src = "images/no_image.png"; // Immagine di fallback
                    img.alt = "Immagine non disponibile";
                }

                // Crea il testo
                let span = document.createElement("span");
                if (isFumetto) {
                    // Per i fumetti, mostra nome_volume e numero_fumetto se disponibili
                    if (elem.nome_volume && elem.numero_fumetto) {
                        span.textContent = elem.nome_volume + " #" + elem.numero_fumetto;
                    } else if (elem.nome_volume) {
                        span.textContent = elem.nome_volume;
                    } else {
                        span.textContent = elem.titolo || "Titolo non disponibile";
                    }
                } else {
                    // Per gli altri tipi, mostra solo il titolo
                    span.textContent = elem.titolo || "Titolo non disponibile";
                }

                li.appendChild(img);
                li.appendChild(span);
                ul.appendChild(li);
            }
        }

        document.addEventListener("DOMContentLoaded", caricaProfilo);
    </script>
</body>
</html>