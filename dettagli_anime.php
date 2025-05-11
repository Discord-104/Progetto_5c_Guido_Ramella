<?php
    session_start();
    if (!isset($_SESSION["utente_id"])) {
        header("Location: login.php");
        exit;
    }

    if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
        echo "<h2>Errore: parametro ID non valido.</h2>";
        exit;
    }

    $id = (int) $_GET["id"];
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dettagli Anime</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Optional: Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <link rel="stylesheet" href="CSS/anime.css">
    <script>
        async function caricaDettagliAnime(id) {
            let url = "ajax/getAnimebyID.php?id=" + id;
            let response = await fetch(url);

            if (!response.ok) {
                document.getElementById("contenuto").innerHTML =
                    '<div class="alert alert-danger" role="alert">' +
                    '<i class="fas fa-exclamation-circle me-2"></i>' +
                    'Errore durante il caricamento dei dati.</div>';
                return;
            }

            let txt = await response.text();
            let datiRicevuti = JSON.parse(txt);

            if (datiRicevuti["status"] == "ERR") {
                document.getElementById("contenuto").innerHTML =
                    '<div class="alert alert-danger" role="alert">' +
                    '<i class="fas fa-exclamation-triangle me-2"></i>' +
                    'Errore: ' + datiRicevuti["msg"] + '</div>';
                return;
            }

            let anime = datiRicevuti["data"];
            let html = "";

            html += '<img src="' + anime.immagine + '" alt="' + anime.titolo + '" class="anime-cover">';
            html += '<h2>' + anime.titolo + '</h2>';
            html += '<p><strong>Descrizione:</strong> ' + anime.descrizione + '</p>';

            html += '<div class="row anime-stats">' +
                    '<div class="col-md-4"><p><strong><i class="fas fa-tv me-2"></i>Episodi:</strong> ' + anime.episodi + '</p></div>' +
                    '<div class="col-md-4"><p><strong><i class="fas fa-star me-2"></i>Punteggio:</strong> ' + anime.punteggio + '</p></div>' +
                    '<div class="col-md-4"><p><strong><i class="fas fa-calendar me-2"></i>Stagione:</strong> ' + anime.stagione + '</p></div>' +
                    '</div>';

            html += '<p><strong>Generi:</strong> ' + anime.generi + '</p>';
            html += '<p><strong>Tag:</strong> <span class="tags">' + anime.tags.join(", ") + '</span></p>';

            html += '<div class="row anime-dates">' +
                    '<div class="col-md-6"><p><strong><i class="fas fa-hourglass-start me-2"></i>Data inizio:</strong> ' + anime.inizio + '</p></div>' +
                    '<div class="col-md-6"><p><strong><i class="fas fa-hourglass-end me-2"></i>Data fine:</strong> ' + anime.fine + '</p></div>' +
                    '</div>';

            html += '<p><strong><i class="fas fa-film me-2"></i>Studio:</strong> ' + anime.studio + '</p>';

            if (anime.trailer) {
                html += '<h3><i class="fas fa-play-circle me-2"></i>Trailer</h3>' +
                        '<div class="trailer-container"><iframe src="' + anime.trailer + '" frameborder="0" allowfullscreen></iframe></div>';
            }

            html += '<h3><i class="fas fa-users me-2"></i>Personaggi principali</h3><div class="griglia">';
            for (let i = 0; i < anime.personaggi.length; i++) {
                let p = anime.personaggi[i];
                html += '<div class="personaggio"><img src="' + p.immagine + '" alt="' + p.nome + '"><p>' + p.nome + '</p></div>';
            }
            html += '</div>';

            html += '<h3><i class="fas fa-id-card me-2"></i>Staff</h3><div class="griglia">';
            for (let i = 0; i < anime.staff.length; i++) {
                let s = anime.staff[i];
                html += '<div class="staff-membro"><img src="' + s.immagine + '" alt="' + s.nome + '"><p><strong>' + s.nome + '</strong><br><span>' + s.ruolo + '</span></p></div>';
            }
            html += '</div>';

            html += '<h3><i class="fas fa-project-diagram me-2"></i>Relazioni</h3><div class="griglia">';
            for (let i = 0; i < anime.relazioni.length; i++) {
                let r = anime.relazioni[i];
                let paginaDettaglio = "dettagli_anime.php";
                if (r.tipo == "MANGA") {
                    paginaDettaglio = "dettagli_manga.php";
                }

                html += '<div class="relazione"><a href="' + paginaDettaglio + '?id=' + r.id + '">';
                if (r.immagine) {
                    html += '<img src="' + r.immagine + '" alt="' + r.titolo + '">';
                }
                html += '<p>' + r.relazione + ' - ' + r.tipo + ': ' + r.titolo + '</p></a></div>';
            }
            html += '</div>';

            html += '<h3><i class="fas fa-thumbs-up me-2"></i>Raccomandazioni</h3><div class="griglia">';
            for (let i = 0; i < anime.raccomandazioni.length; i++) {
                let rec = anime.raccomandazioni[i];
                html += '<div class="raccomandazione"><a href="dettagli_anime.php?id=' + rec.id + '">' +
                        '<img src="' + rec.immagine + '" alt="' + rec.titolo + '"><p>' + rec.titolo + '</p></a></div>';
            }
            html += '</div>';

            document.getElementById("contenuto").innerHTML = html;
        }
        
        function populateActivityForm(activity) {
            if (activity.status) {
                document.getElementById('status').value = activity.status;
            }
            if (activity.punteggio) {
                document.getElementById('punteggio').value = activity.punteggio;
            }
            if (activity.episodi_visti >= 0) {
                document.getElementById('episodi_visti').value = activity.episodi_visti;
            }
            if (activity.start_date) {
                document.getElementById('start_date').value = activity.start_date;
            }
            if (activity.end_date) {
                document.getElementById('end_date').value = activity.end_date;
            }
            if (activity.note) {
                document.getElementById('note').value = activity.note;
            }
            if (activity.rewatch) {
                document.getElementById('rewatch').value = activity.rewatch;
            }
            if (activity.preferito == 1) {
                document.getElementById('preferito').checked = true;
            }
        }


        function toggleEditor() {
            let editor = document.getElementById('editor');
            let button = document.getElementById('editorButton');
            
            if (editor.style.display === 'none' || editor.style.display === '') {
                editor.style.display = 'block';
                button.innerHTML = '<i class="fas fa-times me-2"></i>Chiudi editor';
                button.classList.replace('btn-primary', 'btn-secondary');
            } else {
                editor.style.display = 'none';
                button.innerHTML = '<i class="fas fa-edit me-2"></i>Modifica lista';
                button.classList.replace('btn-secondary', 'btn-primary');
            }
        }

        async function salvaAttivita() {
            let status = document.getElementById('status').value;
            let punteggio = document.getElementById('punteggio').value;
            let episodiVisti = document.getElementById('episodi_visti').value;
            let startDate = document.getElementById('start_date').value;
            let endDate = document.getElementById('end_date').value;
            let note = document.getElementById('note').value;
            let rewatch = document.getElementById('rewatch').value;

            let preferito = 0;
            if (document.getElementById('preferito').checked) {
                preferito = 1;
            }

            if (punteggio !== "" && (punteggio < 0 || punteggio > 10)) {
                showAlert("Il punteggio deve essere compreso tra 0 e 10", "danger");
                return;
            }

            if (episodiVisti < 0) {
                showAlert("Il numero di episodi visti non può essere negativo", "danger");
                return;
            }

            let url = "ajax/attivita_anime.php";
            url += "?anime_id=" + <?php echo $id; ?>;
            url += "&status=" + status;
            url += "&punteggio=" + punteggio;
            url += "&episodi_visti=" + episodiVisti;
            url += "&start_date=" + startDate;
            url += "&end_date=" + endDate;
            url += "&note=" + note;
            url += "&rewatch=" + rewatch;
            url += "&preferito=" + preferito;

            let response = await fetch(url);

            if (response.ok) {
                let data = await response.json();
                if (data.status == "OK") {
                    showAlert("Attività salvata con successo!", "success");
                    setTimeout(function () {
                        toggleEditor();
                    }, 1500);
                } else {
                    showAlert("Errore nel salvataggio dell'attività: " + data.message, "danger");
                }
            } else {
                showAlert("Errore di rete. Impossibile completare il salvataggio", "danger");
            }
        }


        function showAlert(message, type) {
            let alertBox = document.createElement('div');
            alertBox.className = "alert alert-" + type + " alert-dismissible fade show";
            alertBox.role = "alert";

            alertBox.innerHTML =
                message +
                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';

            let editor = document.getElementById('editor');
            editor.insertBefore(alertBox, editor.firstChild);

            setTimeout(function () {
                alertBox.classList.remove('show');
                setTimeout(function () {
                    alertBox.remove();
                }, 300);
            }, 5000);
        }

        document.addEventListener("DOMContentLoaded", function () {
            caricaDettagliAnime(<?php echo $id; ?>);
        });

    </script>
</head>
<body>
    <!-- Parallax background effect -->
    <div class="parallax-bg"></div>
    
    <div class="container">
        <nav aria-label="breadcrumb" class="mt-2 mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="home.php" class="text-decoration-none"><i class="fas fa-home me-1"></i>Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Dettagli</li>
            </ol>
        </nav>
        
        <div id="contenuto">
            <div class="d-flex justify-content-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Caricamento in corso...</span>
                </div>
                <p class="ms-3">Caricamento dettagli anime...</p>
            </div>
        </div>

        <div class="text-center mt-4 mb-3">
            <button id="editorButton" class="btn btn-primary" onclick="toggleEditor()">
                <i class="fas fa-edit me-2"></i>Modifica lista
            </button>
        </div>

        <div id="editor" class="editor" style="display: none;">
            <h3><i class="fas fa-list-alt me-2"></i>La tua attività</h3>

            <div class="row">
                <div class="col-md-6">
                    <label for="status" class="form-label">Status:</label>
                    <select id="status" class="form-select">
                        <option value="Watching">Watching</option>
                        <option value="Complete">Complete</option>
                        <option value="Planning" selected>Planning</option>
                        <option value="Dropped">Dropped</option>
                        <option value="On Hold">On Hold</option>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label for="punteggio" class="form-label">Punteggio:</label>
                    <input type="number" id="punteggio" class="form-control" step="0.1" min="0" max="10" placeholder="Da 0 a 10">
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label for="episodi_visti" class="form-label">Episodi Visti:</label>
                    <input type="number" id="episodi_visti" class="form-control" min="0">
                </div>
                
                <div class="col-md-6">
                    <label for="rewatch" class="form-label">Rewatch (quante volte):</label>
                    <input type="number" id="rewatch" class="form-control" min="0">
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label for="start_date" class="form-label">Data inizio:</label>
                    <input type="date" id="start_date" class="form-control">
                </div>
                
                <div class="col-md-6">
                    <label for="end_date" class="form-label">Data fine:</label>
                    <input type="date" id="end_date" class="form-control">
                </div>
            </div>

            <div class="mt-3">
                <label for="note" class="form-label">Note:</label>
                <textarea id="note" class="form-control" rows="4" placeholder="Scrivi qui le tue note personali..."></textarea>
            </div>

            <div class="form-check mt-3">
                <input type="checkbox" id="preferito" class="form-check-input">
                <label for="preferito" class="form-check-label">Aggiungi ai preferiti</label>
            </div>

            <div class="text-center mt-4">
                <button onclick="salvaAttivita()" class="btn btn-lg btn-success">
                    <i class="fas fa-save me-2"></i>Salva Attività
                </button>
            </div>
        </div>
        
        <footer class="mt-5 text-center text-muted">
            <p><small>&copy; <?php echo date('Y'); ?> NerdVerse - Tutti i diritti riservati</small></p>
        </footer>
    </div>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>