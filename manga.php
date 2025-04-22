<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Ricerca Manga</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f0f0;
            padding: 20px;
        }
        .manga {
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
    </style>
</head>
<body>

<h1>Ricerca Manga</h1>
<input type="text" id="query" placeholder="Es. Naruto" />
<button onclick="cercaManga()">Cerca</button>

<div id="risultati"></div>

<script>
    async function cercaManga() {
        var query = document.getElementById("query").value;
        var container = document.getElementById("risultati");
        container.innerHTML = "Caricamento...";

        try {
            // Genera l'URL e esegue la richiesta fetch
            var url = "ajax/cercaManga.php?query=" + encodeURIComponent(query);
            console.log("URL richiesta: ", url); // Aggiungi un log dell'URL per il debug

            var response = await fetch(url);

            if (!response.ok) {
                throw new Error("Errore nella fetch dei manga: " + response.status);
            }

            // Leggi la risposta come testo e logga la risposta raw
            var txt = await response.text(); 
            console.log("Risposta raw del server:", txt); // Log della risposta del server (HTML o JSON)

            // Parso il testo in formato JSON
            var dati = JSON.parse(txt);  
            console.log("Dati decodificati:", dati);

            if (dati["status"] === "ERR") {
                container.innerHTML = "<p style='color: red;'>" + dati["msg"] + "</p>";
                return;
            }

            container.innerHTML = ""; // Svuota i risultati precedenti

            // Aggiungi i risultati alla pagina
            for (var i = 0; i < dati["dati"].length; i++) {
                var manga = dati["dati"][i];
                container.innerHTML +=
                    "<div class='manga'>" +
                        "<img src='" + manga.image + "' alt='" + manga.titolo + "' />" +
                        "<div class='info'>" +
                            "<h2>" + manga.titolo + "</h2>" +
                            "<p><strong>Capitoli:</strong> " + manga.capitoli + "</p>" +
                            "<p>" + manga.descrizione + "</p>" +
                            "<a href='" + manga.url + "' target='_blank'>Vedi su AniList</a>" +
                        "</div>" +
                    "</div>";
            }

        } catch (err) {
            console.error("Errore durante la ricerca:", err); // Aggiungi il log dell'errore
            container.innerHTML = "<p style='color: red;'>Errore durante la ricerca. Controlla la console per maggiori dettagli.</p>";
        }
    }
</script>

</body>
</html>
