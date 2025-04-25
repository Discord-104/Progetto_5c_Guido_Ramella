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
    async function caricaDettagliVideogioco(guid) {
        let url = "ajax/videogame/getVideogamebyID.php?guid=" + guid;

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
            return datiRicevuti["dato"];
        }
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

        if (gioco != null) {
            let html = "";

            // Immagine
            if (gioco.immagine != "N/D") {
                html += "<div class='info'><span class='etichetta'>Immagine:</span><br><img src='" + gioco.immagine + "' alt='Immagine del gioco' class='immagine'></div>";
            } else {
                html += "<div class='info'><span class='etichetta'>Immagine:</span> Non disponibile</div>";
            }

            html += "<div class='info'><span class='etichetta'>Nome:</span> " + gioco.nome + "</div>";
            html += "<div class='info'><span class='etichetta'>Descrizione:</span> " + gioco.descrizione + "</div>";

            function creaLista(titolo, array) {
                let risultato = "<div class='info'><span class='etichetta'>" + titolo + ":</span> ";
                if (array.length > 0) {
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
                if (array.length > 0) {
                    risultato += "<ul>";
                    for (let i = 0; i < array.length; i++) {
                        risultato += "<li><img src='" + array[i].immagine + "' alt='" + array[i].nome + "' style='height:40px; vertical-align:middle; margin-right:10px;'> " + array[i].nome + "</li>";
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
            html += "<div class='info'><span class='etichetta'>Data di uscita:</span> " + gioco.data_uscita + "</div>";
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
