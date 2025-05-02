<?php
    require_once("classi/db.php");
    require_once("classi/Utente.php");
    session_start();
    
    // Verifica se l'utente è loggato
    if (!isset($_SESSION["utente_id"])) {
        header("Location: login.php"); // Se non è loggato, reindirizza alla pagina di login
        exit();
    }

    // Ottieni i dati dell'utente loggato
    $utente_id = $_SESSION["utente_id"];
    $stmt = $conn->prepare("SELECT * FROM utenti WHERE id = ?");
    $stmt->bind_param("i", $utente_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $utente = $result->fetch_assoc();
    } else {
        echo "Utente non trovato!";
        exit();
    }
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/profilo.css">
    <link rel="stylesheet" href="CSS/home.css">
    <title>Home</title>
</head>
<body>

    <div class="top-bar">
        <h1>Benvenuto <?php echo $utente['username']; ?></h1>
        <img src="<?php echo $utente['profile_image']; ?>" alt="Immagine profilo" class="profile-image">
    </div>

    <div class="search-container">
        <div class="search-bar">
            <input type="text" id="query" placeholder="Cerca titoli..." />
            <select id="tipoRicerca">
                <option value="fumetti">Fumetti</option>
                <option value="anime">Anime</option>
                <option value="manga">Manga</option>
                <option value="videogame">Videogame</option>
            </select>
        </div>
        <div id="risultati" class="search-results">

        </div>
    </div>
        
    <!-- SEZIONE ATTIVITÀ -->
    <div class="activities" id="sezione_attivita">
        <!-- Le attività verranno caricate dinamicamente qui -->
    </div>

    <a class="logout-link" href="logout.php">Esci</a>

    <script>
        async function caricaAttivita() {
            let contenitore = document.getElementById("sezione_attivita");
            contenitore.innerHTML = "";

            let response = await fetch("ajax/carica_attivita_globale.php");

            if (!response.ok) {
                console.error("Errore nella fetch delle attività globali");
                return;
            }

            let txt = await response.text();
            let datiRicevuti = JSON.parse(txt);

            if (datiRicevuti["status"] === "ERR") {
                console.error(datiRicevuti["msg"]);
                return;
            }

            let attività = datiRicevuti["data"];

            for (let i = 0; i < attività.length; i++) {
                let riga = document.createElement("div");
                riga.className = "attivita-item";

                let descrizione = "";
                let stato = "";
                let username = attività[i]["username"];
                let titolo = attività[i]["titolo"];

                // === MANGA ===
                if ("capitoli_letti" in attività[i]) {
                    let capitoli = attività[i]["capitoli_letti"];

                    if (attività[i]["status"] === "Planning") {
                        stato = "Sta pianificando di leggere";
                    } else if (attività[i]["status"] === "Reading") {
                        stato = "Sta leggendo";
                    } else if (attività[i]["status"] === "Complete") {
                        stato = "Ha finito di leggere";
                    } else if (attività[i]["status"] === "Paused") {
                        stato = "Messo in pausa";
                    } else if (attività[i]["status"] === "Dropped") {
                        stato = "Ha smesso di leggere";
                    }

                    let anno = attività[i]["anno"];
                    let formato = attività[i]["formato"];

                    descrizione = "<strong>" + username + "</strong> " + stato + " <strong>" +
                                titolo + "</strong><br>Capitoli letti: " + capitoli +
                                "<br>Anno: " + anno + " - Formato: " + formato;

                }
                // === ANIME ===
                else if ("episodi_visti" in attività[i]) {
                    let episodi = attività[i]["episodi_visti"];

                    if (attività[i]["status"] === "Planning") {
                        stato = "Sta pianificando di guardare";
                    } else if (attività[i]["status"] === "Watching") {
                        stato = "Sta guardando";
                    } else if (attività[i]["status"] === "Complete") {
                        stato = "Ha finito di guardare";
                    } else if (attività[i]["status"] === "Paused") {
                        stato = "Messo in pausa";
                    } else if (attività[i]["status"] === "Dropped") {
                        stato = "Ha smesso di guardare";
                    }

                    let anno = attività[i]["anno_uscita"];
                    let formato = attività[i]["formato"];

                    descrizione = "<strong>" + username + "</strong> " + stato + " <strong>" +
                                titolo + "</strong><br>Episodi visti: " + episodi +
                                "<br>Anno: " + anno + " - Formato: " + formato;
                }
                // === FUMETTI ===
                else if ("pagine_lette" in attività[i]) {
                    let pagine = attività[i]["pagine_lette"];

                    if (attività[i]["status"] === "Planning") {
                        stato = "Sta pianificando di leggere";
                    } else if (attività[i]["status"] === "Reading") {
                        stato = "Sta leggendo";
                    } else if (attività[i]["status"] === "Complete") {
                        stato = "Ha finito di leggere";
                    } else if (attività[i]["status"] === "Paused") {
                        stato = "Messo in pausa";
                    } else if (attività[i]["status"] === "Dropped") {
                        stato = "Ha smesso di leggere";
                    }

                    let anno = attività[i]["anno_uscita"];
                    let volume = attività[i]["numero_volume"];

                    descrizione = "<strong>" + username + "</strong> " + stato + " <strong>" +
                                titolo + "</strong><br>Pagine lette: " + pagine +
                                "<br>Anno: " + anno + " - Volume: " + volume;
                }

                riga.innerHTML = "<div class='attivita-card'>" +
                                    "<img src='" + attività[i]["immagine"] + "' alt='Copertina' class='copertina-anime'>" +
                                    "<div class='testo-attivita'>" + descrizione + "</div>" +
                                "</div>";

                contenitore.appendChild(riga);
            }

            if (contenitore.children.length === 0) {
                let messaggio = document.createElement("div");
                messaggio.className = "nessuna-attivita";
                messaggio.innerText = "Nessuna attività trovata.";
                contenitore.appendChild(messaggio);
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            caricaAttivita();
        });

        async function cerca() {
            let query = document.getElementById("query").value;
            let container = document.getElementById("risultati");
            container.innerHTML = "Caricamento...";

            // Ottieni il tipo di ricerca dal menu a tendina
            let tipo = document.getElementById("tipoRicerca").value;

            // Costruisci l'URL della richiesta in base al tipo
            let url = "";
            if (tipo === "fumetti") {
                url = "ajax/cercaFumetti.php?query=" + query;
            } else if (tipo === "anime") {
                url = "ajax/cercaAnime.php?query=" + query;
            } else if (tipo === "manga") {
                url = "ajax/cercaManga.php?query=" + query;
            } else if (tipo === "videogame") {
                url = "ajax/cercaVideogame.php?query=" + query;
            }

            // Esegui la richiesta asincrona con fetch
            let response = await fetch(url);

            // Controlla se la richiesta HTTP è andata a buon fine
            if (!response.ok) {
                container.innerHTML = "<p style='color: red;'>Errore nella fetch della ricerca: " + response.status + "</p>";
                return;
            }

            // Verifica che la risposta sia in formato JSON
            let txt = await response.text();
            let datiRicevuti;

            // Prova a fare il parse del JSON, ma senza try/catch
            if (txt.startsWith('<br>')) {
                container.innerHTML = "<p style='color: red;'>Errore: formato risposta non valido</p>";
                return;
            }

            datiRicevuti = JSON.parse(txt);

            if (datiRicevuti["status"] == "ERR") {
                container.innerHTML = "<p style='color: red;'>Errore: " + datiRicevuti["msg"] + "</p>";
                return;
            }

            // Genera i risultati
            container.innerHTML = "";
            if (datiRicevuti["data"].length === 0) {
                container.innerHTML = "<p>Nessun risultato trovato.</p>";
            }

            // Per ogni elemento, visualizza il risultato
            for (let i = 0; i < datiRicevuti["data"].length; i++) {
                let elemento = datiRicevuti["data"][i];
                let card = document.createElement("div");
                card.className = "card";
                card.innerHTML = "<img src='" + elemento["immagine"] + "' alt='Immagine' class='immagine-card'>" +
                                 "<div class='titolo-card'>" + elemento["titolo"] + "</div>";
                container.appendChild(card);
            }
        }

        document.getElementById("query").addEventListener("input", cerca);
    </script>

</body>
</html>
