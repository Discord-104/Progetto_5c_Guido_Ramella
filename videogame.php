<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Ricerca Videogames</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f0f0;
            padding: 20px;
        }
        .game {
            background: white;
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
            display: flex;
            gap: 15px;
        }
        img {
            max-width: 100px;
            border-radius: 6px;
        }
        .info h2 {
            margin: 0;
        }
        #searchContainer {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<h1>Ricerca Videogames</h1>

<div id="searchContainer">
    <input type="text" id="query" />
</div>

<div id="risultati"></div>

<script>
async function cercaGames() {
            // Ottieni il termine di ricerca dalla barra di ricerca
            var query = document.getElementById("query").value;
            var container = document.getElementById("risultati");
            
            // Mostra un messaggio di caricamento
            container.innerHTML = "Caricamento...";

            try {
                // Se la query è vuota, non fare nulla
                if (query.trim() === "") {
                    container.innerHTML = "<p>Inserisci un termine di ricerca.</p>";
                    return;
                }

                // Genera l'URL della ricerca (fai attenzione ai caratteri speciali)
                var url = "ajax/cercaVideogame.php?query=" + encodeURIComponent(query);

                // Esegui la chiamata fetch
                var response = await fetch(url);

                if (!response.ok) {
                    throw new Error("Errore nella fetch dei videogames: " + response.status);
                }

                // Leggi la risposta come testo
                var txt = await response.text(); 

                // Se la risposta è HTML, non possiamo fare il parsing come JSON
                if (txt.startsWith("<html>")) {
                    throw new Error("Il server ha restituito una pagina HTML invece di JSON.");
                }

                // Parso la risposta come JSON
                var dati = JSON.parse(txt);

                if (dati["status"] === "ERR") {
                    container.innerHTML = "<p style='color: red;'>" + dati["msg"] + "</p>";
                    return;
                }

                container.innerHTML = ""; // Svuota i risultati precedenti

                // Aggiungi i risultati alla pagina
                if (dati["dati"].length === 0) {
                    container.innerHTML = "<p>Nessun risultato trovato.</p>";
                }

                for (var i = 0; i < dati["dati"].length; i++) {
                    var game = dati["dati"][i];
                    container.innerHTML +=
                        "<div class='game'>" +
                            "<img src='" + game.immagine + "' alt='" + game.titolo + "' />" +
                            "<div class='info'>" +
                                "<h2>" + game.titolo + "</h2>" +
                                "<p>" + game.descrizione + "</p>" +
                                "<a href='" + game.link + "' target='_blank'>Vedi su GiantBomb</a>" +
                            "</div>" +
                        "</div>";
                }

            } catch (err) {
                console.error("Errore durante la ricerca:", err); 
                container.innerHTML = "<p style='color: red;'>Errore durante la ricerca. Controlla la console per maggiori dettagli.</p>";
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            // Attiviamo la ricerca automaticamente una volta che la pagina è pronta
            cercaGames();

            // Aggiungiamo un listener per la barra di ricerca
            document.getElementById("query").addEventListener("input", function() {
                // Ogni volta che l'utente digita, facciamo una nuova ricerca
                cercaGames();
            });
        });
</script>

</body>
</html>
