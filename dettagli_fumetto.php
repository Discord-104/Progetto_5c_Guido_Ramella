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
</div>

<script>
    async function caricaDettagliFumetto(id) {
        let url = "ajax/getFumettoByID.php?id=" + id;

        let response = await fetch(url);

        if (!response.ok) {
            throw new Error("Errore nella fetch.");
        }

        let txt = await response.text();
        console.log(txt);

        let datiRicevuti = JSON.parse(txt);
        console.log(datiRicevuti);

        if (datiRicevuti["status"] == "ERR") {
            alert(datiRicevuti["msg"]);
            return null;
        } else {
            return datiRicevuti["data"];
        }
    }

    document.addEventListener("DOMContentLoaded", async function () {
        function getQueryParam(nome) {
            let params = new URLSearchParams(window.location.search);
            return params.get(nome);
        }

        let id = getQueryParam("id");

        if (!id) {
            document.getElementById("dettagli").innerHTML = "<div class='errore'>ID mancante nell'URL.</div>";
            return;
        }

        let fumetto = await caricaDettagliFumetto(id);

        if (fumetto != null) {
            let html = "";

            if (fumetto.immagine != "") {
                html += "<div class='info'><span class='etichetta'>Immagine:</span><br><img src='" + fumetto.immagine + "' alt='Copertina del fumetto' class='immagine'></div>";
            } else {
                html += "<div class='info'><span class='etichetta'>Immagine:</span> Non disponibile</div>";
            }

            html += "<div class='info'><span class='etichetta'>Titolo:</span> " + fumetto.titolo + "</div>";
            html += "<div class='info'><span class='etichetta'>Volume:</span> " + fumetto.volume + "</div>";
            html += "<div class='info'><span class='etichetta'>Numero:</span> " + fumetto.numero + "</div>";
            html += "<div class='info'><span class='etichetta'>Descrizione:</span> " + fumetto.descrizione + "</div>";
            html += "<div class='info'><span class='etichetta'>Data di pubblicazione:</span> " + fumetto.data_pubblicazione + "</div>";

            let aliasesArray = [];
            if (fumetto.aliases != "") {
                aliasesArray = fumetto.aliases.split("\n");
            }

            html += "<div class='info'><span class='etichetta'>Alias:</span> ";
            if (aliasesArray.length > 0) {
                html += "<ul>";
                for (let i = 0; i < aliasesArray.length; i++) {
                    html += "<li>" + aliasesArray[i] + "</li>";
                }
                html += "</ul>";
            } else {
                html += "Nessuno";
            }
            html += "</div>";

            html += "<div class='info'><span class='etichetta'>Personaggi:</span> ";
            if (fumetto.personaggi.length > 0) {
                html += "<ul>";
                for (let i = 0; i < fumetto.personaggi.length; i++) {
                    html += "<li>";
                    if (fumetto.personaggi[i].immagine != "") {
                        html += "<img src='" + fumetto.personaggi[i].immagine + "' alt='" + fumetto.personaggi[i].nome + "' style='height:40px; vertical-align:middle; margin-right:10px;'>";
                    }
                    html += fumetto.personaggi[i].nome + "</li>";
                }
                html += "</ul>";
            } else {
                html += "Nessuno";
            }
            html += "</div>";

            document.getElementById("dettagli").innerHTML = html;
        }
    });
</script>
</body>
</html>
