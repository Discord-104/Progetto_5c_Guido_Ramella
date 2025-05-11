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
    <link rel="stylesheet" href="CSS/dettagli_anime.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome per icone -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="CSS/home_style.css">
    <title>NerdVerse - Home</title>
</head>
<body>
    <!-- Background con effetto parallax -->
    <div class="parallax-bg"></div>

    <div class="container-fluid pb-4">
        <!-- Top navigation bar -->
        <nav class="navbar navbar-expand-lg navbar-dark mb-4">
            <div class="container-fluid">
                <h1 class="navbar-brand mb-0 brand">NerdVerse</h1>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link active" href="#">Home</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="libraryDropdown" role="button" data-bs-toggle="dropdown">
                                Libreria
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark">
                                <li><a class="dropdown-item" href="visualizza_anime.php">Anime</a></li>
                                <li><a class="dropdown-item" href="visualizza_manga.php">Manga</a></li>
                                <li><a class="dropdown-item" href="visualizza_fumetti.php">Fumetti</a></li>
                                <li><a class="dropdown-item" href="visualizza_videogiochi.php">Videogiochi</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="pannello_personale.php">Il mio profilo</a>
                        </li>
                    </ul>
                    
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <span class="text-light">Benvenuto, <strong><?php echo $utente['username']; ?></strong></span>
                        </div>
                        <div class="profile-container">
                            <img src="<?php echo $utente['profile_image']; ?>" alt="Immagine profilo" class="profile-image">
                            <div class="profile-menu">
                                <a href="pannello_personale.php"><i class="fas fa-user-circle me-2"></i> Profilo</a>
                                <a href="visualizza_anime.php"><i class="fas fa-tv me-2"></i> Anime</a>
                                <a href="visualizza_manga.php"><i class="fas fa-book me-2"></i> Manga</a>
                                <a href="visualizza_fumetti.php"><i class="fas fa-book-open me-2"></i> Fumetti</a>
                                <a href="visualizza_videogiochi.php"><i class="fas fa-gamepad me-2"></i> Videogiochi</a>
                                <div class="dropdown-divider"></div>
                                <a href="logout.php" class="text-danger"><i class="fas fa-sign-out-alt me-2"></i> Esci</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Search section -->
        <div class="row justify-content-center mb-4">
            <div class="col-lg-8">
                <div class="card search-card">
                    <div class="card-body">
                        <h4 class="card-title mb-3"><i class="fas fa-search me-2"></i>Cerca nei tuoi contenuti</h4>
                        <div class="input-group">
                            <input type="text" id="query" class="form-control" placeholder="Cerca titoli...">
                            <select id="tipoRicerca" class="form-select" style="max-width: 150px;">
                                <option value="fumetti">Fumetti</option>
                                <option value="anime">Anime</option>
                                <option value="manga">Manga</option>
                                <option value="videogame">Videogiochi</option>
                            </select>
                        </div>
                        <div id="risultati" class="search-results mt-3"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs for content types -->
        <div class="row justify-content-center mb-4">
            <div class="col-lg-10">
                <ul class="nav nav-tabs" id="contentTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all-content" type="button">
                            <i class="fas fa-stream me-2"></i>Tutte le attività
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="anime-tab" data-bs-toggle="tab" data-bs-target="#anime-content" type="button">
                            <i class="fas fa-tv me-2"></i>Anime
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="manga-tab" data-bs-toggle="tab" data-bs-target="#manga-content" type="button">
                            <i class="fas fa-book me-2"></i>Manga
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="comics-tab" data-bs-toggle="tab" data-bs-target="#comics-content" type="button">
                            <i class="fas fa-book-open me-2"></i>Fumetti
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="games-tab" data-bs-toggle="tab" data-bs-target="#games-content" type="button">
                            <i class="fas fa-gamepad me-2"></i>Videogiochi
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content" id="contentTabsContent">
                    <!-- All activities tab -->
                    <div class="tab-pane fade show active" id="all-content" role="tabpanel">
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="activities" id="sezione_attivita">
                                    <!-- Le attività verranno caricate dinamicamente qui -->
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Anime tab con colonna utente -->
                    <div class="tab-pane fade" id="anime-content" role="tabpanel">
                        <div class="table-responsive mt-4">
                            <table class="table table-dark table-hover" id="anime-table">
                                <thead>
                                    <tr>
                                        <th>Utente</th>
                                        <th>Immagine</th>
                                        <th>Titolo</th>
                                        <th>Formato</th>
                                        <th>Anno</th>  
                                        <th>Stato</th>
                                        <th>Episodi visti</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Dati anime caricati dinamicamente -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Manga tab con colonna utente -->
                    <div class="tab-pane fade" id="manga-content" role="tabpanel">
                        <div class="table-responsive mt-4">
                            <table class="table table-dark table-hover" id="manga-table">
                                <thead>
                                    <tr>
                                        <th>Utente</th>
                                        <th>Immagine</th>
                                        <th>Titolo</th>
                                        <th>Formato</th>
                                        <th>Anno</th>
                                        <th>Stato</th>
                                        <th>Capitoli letti</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Dati manga caricati dinamicamente -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Comics tab con colonna utente -->
                    <div class="tab-pane fade" id="comics-content" role="tabpanel">
                        <div class="table-responsive mt-4">
                            <table class="table table-dark table-hover" id="fumetti-table">
                                <thead>
                                    <tr>
                                        <th>Utente</th>
                                        <th>Immagine</th>
                                        <th>Titolo</th>
                                        <th>Volume</th>
                                        <th>Numero</th>
                                        <th>Anno</th>
                                        <th>Stato</th>
                                        <th>Pagine lette</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Dati fumetti caricati dinamicamente -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Games tab con colonna utente -->
                    <div class="tab-pane fade" id="games-content" role="tabpanel">
                        <div class="table-responsive mt-4">
                            <table class="table table-dark table-hover" id="giochi-table">
                                <thead>
                                    <tr>
                                        <th>Utente</th>
                                        <th>Immagine</th>
                                        <th>Titolo</th>
                                        <th>Data uscita</th>
                                        <th>Stato</th>
                                        <th>Ore giocate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Dati videogiochi caricati dinamicamente -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Funzione per caricare le attività globali
        async function caricaAttivita() {
            let contenitore = document.getElementById("sezione_attivita");
            contenitore.innerHTML = '<div class="text-center"><div class="spinner-border text-light" role="status"><span class="visually-hidden">Caricamento...</span></div></div>';

            let response = await fetch("ajax/carica_attivita_globale.php");

            if (!response.ok) {
                console.error("Errore nella fetch delle attività globali");
                contenitore.innerHTML = '<div class="alert alert-danger">Errore nel caricamento delle attività</div>';
                return;
            }

            let txt = await response.text();
            let datiRicevuti = JSON.parse(txt);

            if (datiRicevuti["status"] === "ERR") {
                console.error(datiRicevuti["msg"]);
                contenitore.innerHTML = '<div class="alert alert-danger">' + datiRicevuti["msg"] + '</div>';
                return;
            }

            let attivita = datiRicevuti["data"];
            contenitore.innerHTML = "";

            // Arrays per i dati delle tabelle
            let animeData = [];
            let mangaData = [];
            let fumettiData = [];
            let giochiData = [];

            for (let i = 0; i < attivita.length; i++) {
                let item = attivita[i];
                let card = document.createElement("div");
                card.className = "card mb-3 animated pop-in";
                card.style.animationDelay = (i * 0.1) + "s";

                let descrizione = "";
                let stato = "";
                let username = item["username"];
                let profileImage = item["profile_image"] || "images/default-avatar.jpg";
                let titolo = item["titolo"];
                let immagine = item["immagine"];
                let mediaDescrizione = item["descrizione"];
                
                if (!immagine) {
                    immagine = "images/default-cover.jpg";
                }
                
                if (!mediaDescrizione) {
                    mediaDescrizione = "";
                }

                // === MANGA ===
                if ("capitoli_letti" in item) {
                    let capitoli = item["capitoli_letti"];

                    if (item["status"] === "Planning") {
                        stato = "Sta pianificando di leggere";
                    } else if (item["status"] === "Reading") {
                        stato = "Sta leggendo";
                    } else if (item["status"] === "Complete") {
                        stato = "Ha finito di leggere";
                    } else if (item["status"] === "Paused") {
                        stato = "Messo in pausa";
                    } else if (item["status"] === "Dropped") {
                        stato = "Ha smesso di leggere";
                    }

                    let anno = item["anno"];
                    let formato = item["formato"];

                    descrizione = '<div class="user-activity-info"><img src="' + profileImage + '" class="user-activity-avatar me-2" alt="' + username + '">' +
                            '<strong>' + username + '</strong></div> ' + stato + ' <strong>' +
                            titolo + '</strong><br>Capitoli letti: ' + capitoli +
                            '<br>Anno: ' + anno + ' - Formato: ' + formato;
                    
                    // Aggiungiamo la descrizione del media se disponibile
                    if (mediaDescrizione) {
                        descrizione += '<br><br><em>' + mediaDescrizione + '</em>';
                    }
                    
                    // Aggiungi alla tabella manga
                    mangaData.push({
                        profile_image: profileImage,
                        username: username,
                        image: immagine,
                        title: titolo,
                        format: formato,
                        year: anno,
                        status: stato,
                        chapters: capitoli
                    });
                }
                // === ANIME ===
                else if ("episodi_visti" in item) {
                    let episodi = item["episodi_visti"];

                    if (item["status"] === "Planning") {
                        stato = "Sta pianificando di guardare";
                    } else if (item["status"] === "Watching") {
                        stato = "Sta guardando";
                    } else if (item["status"] === "Complete") {
                        stato = "Ha finito di guardare";
                    } else if (item["status"] === "Paused") {
                        stato = "Messo in pausa";
                    } else if (item["status"] === "Dropped") {
                        stato = "Ha smesso di guardare";
                    }

                    let anno = item["anno_uscita"];
                    let formato = item["formato"];

                    descrizione = '<div class="user-activity-info"><img src="' + profileImage + '" class="user-activity-avatar me-2" alt="' + username + '">' +
                            '<strong>' + username + '</strong></div> ' + stato + ' <strong>' +
                            titolo + '</strong><br>Episodi visti: ' + episodi +
                            '<br>Anno: ' + anno + ' - Formato: ' + formato;
                    
                    // Aggiungiamo la descrizione del media se disponibile
                    if (mediaDescrizione) {
                        descrizione += '<br><br><em>' + mediaDescrizione + '</em>';
                    }
                    
                    // Aggiungi alla tabella anime
                    animeData.push({
                        profile_image: profileImage,
                        username: username,
                        image: immagine,
                        title: titolo,
                        format: formato,
                        year: anno,
                        status: stato,
                        episodes: episodi
                    });
                }
                // === FUMETTI ===
                else if ("pagine_lette" in item) {
                    let pagine = item["pagine_lette"];

                    if (item["status"] === "Planning") {
                        stato = "Sta pianificando di leggere";
                    } else if (item["status"] === "Reading") {
                        stato = "Sta leggendo";
                    } else if (item["status"] === "Complete") {
                        stato = "Ha finito di leggere";
                    } else if (item["status"] === "Paused") {
                        stato = "Messo in pausa";
                    } else if (item["status"] === "Dropped") {
                        stato = "Ha smesso di leggere";
                    }

                    let anno = item["anno_uscita"];
                    let nome_volume = item["nome_volume"];
                    let numero_fumetto = item["numero_fumetto"];

                    descrizione = '<div class="user-activity-info"><img src="' + profileImage + '" class="user-activity-avatar me-2" alt="' + username + '">' +
                            '<strong>' + username + '</strong></div> ' + stato + ' <strong>' +
                            titolo + '</strong><br>Pagine lette: ' + pagine +
                            '<br>Anno: ' + anno + ' - Volume: ' + nome_volume + ' - Numero fumetto: ' + numero_fumetto;
                    
                    // Aggiungiamo la descrizione del media se disponibile
                    if (mediaDescrizione) {
                        descrizione += '<br><br><em>' + mediaDescrizione + '</em>';
                    }
                    
                    // Aggiungi alla tabella fumetti
                    fumettiData.push({
                        profile_image: profileImage,
                        username: username,
                        image: immagine,
                        title: titolo,
                        volume: nome_volume,
                        number: numero_fumetto,
                        year: anno,
                        status: stato,
                        pages: pagine
                    });
                }
                // === VIDEOGIOCHI ===
                else if ("ore_giocate" in item) {
                    let ore = item["ore_giocate"];

                    if (item["status"] === "Planning") {
                        stato = "Sta pianificando di giocare";
                    } else if (item["status"] === "Playing") {
                        stato = "Sta giocando";
                    } else if (item["status"] === "Complete") {
                        stato = "Ha finito di giocare";
                    } else if (item["status"] === "Paused") {
                        stato = "Messo in pausa";
                    } else if (item["status"] === "Dropped") {
                        stato = "Ha smesso di giocare";
                    }

                    let data_uscita = item["data_uscita"];

                    descrizione = '<div class="user-activity-info"><img src="' + profileImage + '" class="user-activity-avatar me-2" alt="' + username + '">' +
                            '<strong>' + username + '</strong></div> ' + stato + ' <strong>' +
                            titolo + '</strong><br>Ore giocate: ' + ore +
                            '<br>Data uscita: ' + data_uscita;
                    
                    // Aggiungiamo la descrizione del media se disponibile
                    if (mediaDescrizione) {
                        descrizione += '<br><br><em>' + mediaDescrizione + '</em>';
                    }
                    
                    // Aggiungi alla tabella giochi
                    giochiData.push({
                        profile_image: profileImage,
                        username: username,
                        image: immagine,
                        title: titolo,
                        releaseDate: data_uscita,
                        status: stato,
                        hours: ore
                    });
                }

                card.innerHTML = '<div class="row g-0">' +
                    '<div class="col-md-3">' +
                        '<img src="' + immagine + '" class="img-fluid rounded-start card-img-top" alt="' + titolo + '">' +
                    '</div>' +
                    '<div class="col-md-9">' +
                        '<div class="card-body">' +
                            '<h5 class="card-title">' + titolo + '</h5>' +
                            '<p class="card-text">' + descrizione + '</p>' +
                            '<p class="card-text"><small class="text-muted">Aggiornato di recente</small></p>' +
                        '</div>' +
                    '</div>' +
                '</div>';

                contenitore.appendChild(card);
            }

            // Popola le tabelle con i dati raccolti
            popolaTabellaAnime(animeData);
            popolaTabellaManga(mangaData);
            popolaTabellaFumetti(fumettiData);
            popolaTabellaGiochi(giochiData);

            if (contenitore.children.length === 0) {
                let messaggio = document.createElement("div");
                messaggio.className = "alert alert-info text-center";
                messaggio.innerText = "Nessuna attività trovata.";
                contenitore.appendChild(messaggio);
            }
            
            // Attiva le animazioni
            setTimeout(function() {
                let animatedElements = document.querySelectorAll('.animated');
                for (let i = 0; i < animatedElements.length; i++) {
                    animatedElements[i].classList.add('visible');
                }
            }, 100);
        }

        // Funzioni per popolare le tabelle
        function popolaTabellaAnime(dati) {
            let tbody = document.querySelector('#anime-table tbody');
            tbody.innerHTML = '';
            
            if (dati.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center">Nessun anime trovato.</td></tr>';
                return;
            }
            
        for (let item of dati) {
                let tr = document.createElement('tr');
                tr.innerHTML = 
                    '<td><div class="user-table-info"><img src="' + item.profile_image + '" class="user-activity-avatar me-2" alt="' + item.username + '"><span>' + item.username + '</span></div></td>' +
                    '<td><img src="' + item.image + '" class="table-thumbnail" alt="' + item.title + '"></td>' +
                    '<td>' + item.title + '</td>' +
                    '<td>' + item.format + '</td>' +
                    '<td>' + item.year + '</td>' +
                    '<td>' + item.status + '</td>' +
                    '<td>' + item.episodes + '</td>';
                tbody.appendChild(tr);
            }
        }

        function popolaTabellaManga(dati) {
            let tbody = document.querySelector('#manga-table tbody');
            tbody.innerHTML = '';
            
            if (dati.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center">Nessun manga trovato.</td></tr>';
                return;
            }
            
            for (let item of dati) {
                let tr = document.createElement('tr');
                tr.innerHTML = 
                    '<td><div class="user-table-info"><img src="' + item.profile_image + '" class="user-activity-avatar me-2" alt="' + item.username + '"><span>' + item.username + '</span></div></td>' +
                    '<td><img src="' + item.image + '" class="table-thumbnail" alt="' + item.title + '"></td>' +
                    '<td>' + item.title + '</td>' +
                    '<td>' + item.format + '</td>' +
                    '<td>' + item.year + '</td>' +
                    '<td>' + item.status + '</td>' +
                    '<td>' + item.chapters + '</td>';
                tbody.appendChild(tr);
            }
        }

        function popolaTabellaFumetti(dati) {
            let tbody = document.querySelector('#fumetti-table tbody');
            tbody.innerHTML = '';
            
            if (dati.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center">Nessun fumetto trovato.</td></tr>';
                return;
            }
            
            for (let item of dati) {
                let tr = document.createElement('tr');
                tr.innerHTML = 
                    '<td><div class="user-table-info"><img src="' + item.profile_image + '" class="user-activity-avatar me-2" alt="' + item.username + '"><span>' + item.username + '</span></div></td>' +
                    '<td><img src="' + item.image + '" class="table-thumbnail" alt="' + item.title + '"></td>' +
                    '<td>' + item.title + '</td>' +
                    '<td>' + item.volume + '</td>' +
                    '<td>' + item.number + '</td>' +
                    '<td>' + item.year + '</td>' +
                    '<td>' + item.status + '</td>' +
                    '<td>' + item.pages + '</td>';
                tbody.appendChild(tr);
            }
        }

        function popolaTabellaGiochi(dati) {
            let tbody = document.querySelector('#giochi-table tbody');
            tbody.innerHTML = '';
            
            if (dati.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center">Nessun videogioco trovato.</td></tr>';
                return;
            }
            
            for (let item of dati) {
                let tr = document.createElement('tr');
                tr.innerHTML = 
                    '<td><div class="user-table-info"><img src="' + item.profile_image + '" class="user-activity-avatar me-2" alt="' + item.username + '"><span>' + item.username + '</span></div></td>' +
                    '<td><img src="' + item.image + '" class="table-thumbnail" alt="' + item.title + '"></td>' +
                    '<td>' + item.title + '</td>' +
                    '<td>' + item.releaseDate + '</td>' +
                    '<td>' + item.status + '</td>' +
                    '<td>' + item.hours + '</td>';
                tbody.appendChild(tr);
            }
        }

        // Funzionalità di ricerca
        async function cerca() {
            let query = document.getElementById("query").value;
            let container = document.getElementById("risultati");
            container.innerHTML = "Caricamento...";

            // Se la query è vuota, svuota i risultati e esci dalla funzione
            if (query.trim() === "") {
                container.innerHTML = "";
                return;
            }

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

            // Ottieni il testo della risposta
            let txt = await response.text();

            // Verifica che la risposta sia in formato JSON
            if (txt.startsWith('<br>')) {
                container.innerHTML = "<p style='color: red;'>Errore: formato risposta non valido</p>";
                return;
            }

            // Parse del JSON
            let datiRicevuti = JSON.parse(txt);

            if (datiRicevuti["status"] == "ERR") {
                container.innerHTML = "<p style='color: red;'>Errore: " + datiRicevuti["msg"] + "</p>";
                return;
            }

            container.innerHTML = ""; // Svuota i risultati precedenti

            // Verifica che dati esista nel JSON
            if (!datiRicevuti.hasOwnProperty("dati") || datiRicevuti["dati"] === null) {
                container.innerHTML = "<p>Nessun risultato disponibile.</p>";
                return;
            }

            // Aggiungi i risultati alla pagina
            if (datiRicevuti["dati"].length === 0) {
                container.innerHTML = "<p>Nessun risultato trovato.</p>";
                return;
            }

            for (let i = 0; i < datiRicevuti["dati"].length; i++) {
                let elemento = datiRicevuti["dati"][i];
                let htmlContent = "";

                if (tipo === "fumetti") {
                    htmlContent = 
                        "<div class='issue'>" +
                            "<img src='" + elemento.immagine + "' alt='" + elemento.titolo + "' />" +
                            "<div class='info'>" +
                                "<h2>" + elemento.titolo + " <small>(#" + elemento.numero + " - " + elemento.volume + ")</small></h2>" +
                                "<p>" + elemento.descrizione + "</p>" +
                                "<a href='" + elemento.link + "' target='_blank'>Vedi nel dettaglio</a>" +
                            "</div>" +
                        "</div>";
                } else if (tipo === "anime") {
                    htmlContent = 
                        "<div class='anime'>" +
                            "<img src='" + elemento.image + "' alt='" + elemento.titolo + "' />" +
                            "<div class='info'>" +
                                "<h2>" + elemento.titolo + "</h2>" +
                                "<p><strong>Episodi:</strong> " + elemento.episodi + "</p>" +
                                "<p>" + elemento.descrizione + "</p>" +
                                "<a href='" + elemento.url + "' target='_blank'>Vedi nel dettaglio</a>" +
                            "</div>" +
                        "</div>";
                } else if (tipo === "manga") {
                    htmlContent = 
                        "<div class='manga'>" +
                            "<img src='" + elemento.image + "' alt='" + elemento.titolo + "' />" +
                            "<div class='info'>" +
                                "<h2>" + elemento.titolo + "</h2>" +
                                "<p><strong>Capitoli:</strong> " + elemento.capitoli + "</p>" +
                                "<p>" + elemento.descrizione + "</p>" +
                                "<a href='" + elemento.url + "' target='_blank'>Vedi nel dettaglio</a>" +
                            "</div>" +
                        "</div>";
                } else if (tipo === "videogame") {
                    htmlContent = 
                        "<div class='game'>" +
                            "<img src='" + elemento.immagine + "' alt='" + elemento.titolo + "' />" +
                            "<div class='info'>" +
                                "<h2>" + elemento.titolo + "</h2>" +
                                "<p>" + elemento.descrizione + "</p>" +
                                "<a href='" + elemento.link + "' target='_blank'>Vedi nel dettaglio</a>" +
                            "</div>" +
                        "</div>";
                }

                container.innerHTML += htmlContent;
            }
        }

        document.getElementById("query").addEventListener("input", cerca);

        // Gestione menu profilo
        document.querySelector('.profile-container').addEventListener('click', function() {
            let menu = this.querySelector('.profile-menu');
            if (menu.style.display === 'block') {
                menu.style.display = 'none';
            } else {
                menu.style.display = 'block';
            }
        });

        // Nasconde il menu profilo quando si clicca altrove
        document.addEventListener('click', function(event) {
            let profileContainer = document.querySelector('.profile-container');
            if (!profileContainer.contains(event.target)) {
                document.querySelector('.profile-menu').style.display = 'none';
            }
        });

        // Parallax effect per lo sfondo
        window.addEventListener('scroll', function() {
            let parallaxBg = document.querySelector('.parallax-bg');
            let scrollPosition = window.pageYOffset;
            parallaxBg.style.transform = 'translateY(' + scrollPosition * 0.5 + 'px)';
        });

        // Carica le attività all'avvio
        document.addEventListener('DOMContentLoaded', function() {
            caricaAttivita();
            
            // Imposta un'immagine di sfondo per l'effetto parallax
            let parallaxBg = document.querySelector('.parallax-bg');
        });
    </script>
</body>
</html>
