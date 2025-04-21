<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>ComicVine AJAX Viewer</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f0f0;
            padding: 20px;
        }
        .issue {
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

<h1>Ricerca Fumetti</h1>
<input type="text" id="query" placeholder="Es. Batman" />
<button onclick="cercaFumetti()">Cerca</button>

<div id="risultati"></div>

<script>
async function cercaFumetti() {
    var query = document.getElementById("query").value;
    var container = document.getElementById("risultati");
    container.innerHTML = "Caricamento...";

    try {
        // Genera l'URL e esegue la richiesta fetch
        var url = "ajax/cercaFumetti.php?query=" + encodeURIComponent(query);
        console.log("URL richiesta: ", url); // Aggiungi un log dell'URL per il debug

        var response = await fetch(url);

        if (!response.ok) {
            throw new Error("Errore nella fetch dei fumetti: " + response.status);
        }

        // Leggi la risposta come testo e logga la risposta raw
        var txt = await response.text(); 
        console.log("Risposta raw del server:", txt); // Log della risposta del server (HTML o JSON)

        // Se la risposta è HTML, non possiamo fare il parsing come JSON
        if (txt.startsWith("<html>")) {
            throw new Error("Il server ha restituito una pagina HTML invece di JSON.");
        }

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
            var issue = dati["dati"][i];
            container.innerHTML +=
                "<div class='issue'>" +
                    "<img src='" + issue.immagine + "' alt='" + issue.titolo + "' />" +
                    "<div class='info'>" +
                        "<h2>" + issue.titolo + " <small>(#" + issue.numero + " - " + issue.volume + ")</small></h2>" +
                        "<p><strong>Pagine:</strong> " + issue.pagine + "</p>" +
                        "<p>" + issue.descrizione + "</p>" +
                        "<a href='" + issue.link + "' target='_blank'>Vedi su ComicVine</a>" +
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
