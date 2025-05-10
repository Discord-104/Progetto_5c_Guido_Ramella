<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Benvenuto su NerdVerse</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="CSS/index.css">
</head>
<body>
    <!-- Effetto parallax di sfondo -->
    <div class="parallax-bg"></div>

    <div class="container text-center mt-5">
        <h1 class="mb-3 animated fade-in">Benvenuto su <span class="brand">NerdVerse</span></h1>
        <p class="lead mb-4 animated slide-in">Tieni traccia delle tue attività nerd preferite: anime, manga, fumetti e videogiochi!</p>

        <div class="d-flex justify-content-center gap-3 mb-5">
            <a href="login.php" class="btn btn-primary btn-lg shadow animated pop-in">Login</a>
            <a href="registrazione.php" class="btn btn-outline-light btn-lg shadow animated pop-in">Registrati</a>
        </div>

        <div class="row justify-content-center g-4">
            <div class="col-md-3 animated fade-in">
                <div class="card h-100">
                    <img src="images/anime.png" class="card-img-top" alt="Anime">
                    <div class="card-body">
                        <h5 class="card-title">Anime</h5>
                        <p class="card-text">Monitora gli episodi visti, aggiungi preferiti e scopri nuove serie.</p>
                    </div>
                    <div class="card-overlay">
                        <h6>Anime Collection</h6>
                        <p>Tieni traccia di ogni episodio visto, valuta le serie e ricevi consigli personalizzati basati sui tuoi gusti.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 animated fade-in">
                <div class="card h-100">
                    <img src="images/manga.png" class="card-img-top" alt="Manga">
                    <div class="card-body">
                        <h5 class="card-title">Manga</h5>
                        <p class="card-text">Tieni conto di capitoli e volumi letti dei tuoi manga preferiti.</p>
                    </div>
                    <div class="card-overlay">
                        <h6>Manga Library</h6>
                        <p>Organizza la tua collezione, segna i capitoli letti e scopri nuovi titoli in base alle tue preferenze di lettura.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 animated fade-in">
                <div class="card h-100">
                    <img src="images/comic.png" class="card-img-top" alt="Fumetti">
                    <div class="card-body">
                        <h5 class="card-title">Fumetti</h5>
                        <p class="card-text">Gestisci le tue letture di fumetti con stile e precisione.</p>
                    </div>
                    <div class="card-overlay">
                        <h6>Comics Tracker</h6>
                        <p>Cataloga la tua collezione di fumetti, tieni traccia di serie ed edizioni speciali, e non perdere mai un nuovo numero.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 animated fade-in">
                <div class="card h-100">
                    <img src="images/videogame.png" class="card-img-top" alt="Videogiochi">
                    <div class="card-body">
                        <h5 class="card-title">Videogiochi</h5>
                        <p class="card-text">Registra le ore giocate e i tuoi titoli preferiti.</p>
                    </div>
                    <div class="card-overlay">
                        <h6>Gaming Dashboard</h6>
                        <p>Monitora il tuo backlog, le ore di gioco, i tuoi achievement e condividi recensioni con altri appassionati.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Descrizione dinamica che appare scrollando -->
        <div class="dynamic-description" id="description-1">
            <h3><i class="fas fa-users"></i> Connettiti con altri Nerd</h3>
            <p>Unisciti alla community di NerdVerse per scoprire cosa stanno guardando, leggendo e giocando altri appassionati come te. Condividi le tue esperienze e trova nuovi amici con i tuoi stessi interessi.</p>
        </div>

        <h2 class="text-center mt-5 mb-4 animated slide-in">Scopri attività dalla community</h2>
        
        <!-- Contenitore per lo scorrimento orizzontale -->
        <div id="random-images" class="overflow-hidden">
            <div class="image-slider">
                <!-- Le immagini verranno inserite qui dinamicamente -->
            </div>
        </div>

        <!-- Altre descrizioni dinamiche che appaiono scrollando -->
        <div class="dynamic-description" id="description-2">
            <h3><i class="fas fa-chart-line"></i> Statistiche Personalizzate</h3>
            <p>Analizza le tue abitudini di consumo mediale con grafici interattivi e statistiche dettagliate. Scopri quali generi preferisci e quanto tempo dedichi alle tue passioni nerd.</p>
        </div>

        <div class="dynamic-description" id="description-3">
            <h3><i class="fas fa-bell"></i> Notifiche Personalizzate</h3>
            <p>Ricevi avvisi per nuovi episodi delle tue serie preferite, uscite di nuovi capitoli di manga, numeri di fumetti e rilasci di videogiochi. Non perderai mai un aggiornamento importante!</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", async function () {
            // Caricamento immagini random
            let response = await fetch("ajax/immagini_random.php");

            if (response.ok) {
                let txt = await response.text();
                let data = JSON.parse(txt);

                if (data.status === "OK") {
                    let immagini = data.data;
                    let slider = document.querySelector(".image-slider");
                    
                    // Creiamo due set di immagini per l'effetto loop infinito
                    for (let j = 0; j < 2; j++) {
                        for (let i = 0; i < immagini.length; i++) {
                            let item = immagini[i];
                            
                            let card = document.createElement("div");
                            card.className = "card shadow";
                            
                            let img = document.createElement("img");
                            img.src = item.immagine;
                            img.alt = item.titolo;
                            img.className = "card-img-top animated fade-in";
                            
                            let overlay = document.createElement("div");
                            overlay.className = "card-overlay";
                            overlay.innerHTML = "<p>" + item.titolo + "</p>";
                            
                            card.appendChild(img);
                            card.appendChild(overlay);
                            slider.appendChild(card);
                        }
                    }
                }
            }
            
            // Osservatore per le descrizioni dinamiche
            let observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, { threshold: 0.2 });
            
            // Osserva tutti gli elementi con classe .dynamic-description
            document.querySelectorAll('.dynamic-description').forEach(description => {
                observer.observe(description);
            });
            
            // Effetto parallax per lo sfondo
            window.addEventListener('scroll', function() {
                const scrollY = window.scrollY;
                document.querySelector('.parallax-bg').style.transform = `translateY(${scrollY * 0.3}px)`;
            });
        });
    </script>
</body>
</html>