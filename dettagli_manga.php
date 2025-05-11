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
    <title>Dettagli Manga</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome per le icone -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <link rel="stylesheet" href="css/manga.css">    

<script>
    async function caricaDettagliManga(id) {
        let url = "ajax/getMangaByID.php?id=" + id;

        let response = await fetch(url);
        if (!response.ok) {
            throw new Error("Errore nella fetch!");
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

        let manga = datiRicevuti["data"];
        let html = "";

        html += '<img src="' + manga.immagine + '" alt="' + manga.titolo + '" class="manga-cover">';
        html += '<h2>' + manga.titolo + '</h2>';
        html += '<p><strong>Descrizione:</strong> ' + manga.descrizione + '</p>';
        
        html += '<div class="row manga-stats">' +
                '<div class="col-md-4"><p><strong><i class="fas fa-book me-2"></i>Capitoli:</strong> ' + manga.capitoli + '</p></div>' +
                '<div class="col-md-4"><p><strong><i class="fas fa-star me-2"></i>Punteggio:</strong> ' + manga.punteggio + '</p></div>' +
                '<div class="col-md-4"><p><strong><i class="fas fa-tags me-2"></i>Generi:</strong> ' + manga.generi + '</p></div>' +
                '</div>';
        
        html += '<p><strong>Tag:</strong> <span class="tags">' + manga.tags.join(", ") + '</span></p>';
        
        html += '<div class="row anime-dates">' +
                '<div class="col-md-6"><p><strong><i class="fas fa-hourglass-start me-2"></i>Data inizio:</strong> ' + manga.inizio + '</p></div>' +
                '<div class="col-md-6"><p><strong><i class="fas fa-hourglass-end me-2"></i>Data fine:</strong> ' + manga.fine + '</p></div>' +
                '</div>';

        html += '<h3><i class="fas fa-users me-2"></i>Personaggi principali</h3>';
        html += '<div class="griglia">';
        for (let p of manga.personaggi) {
            html += '<div class="personaggio">';
            html += '<img src="' + p.immagine + '" alt="' + p.nome + '">';
            html += '<p>' + p.nome + '</p>';
            html += '</div>';
        }
        html += '</div>';

        html += '<h3><i class="fas fa-id-card me-2"></i>Staff</h3>';
        html += '<div class="griglia">';
        for (let s of manga.staff) {
            html += '<div class="staff-membro">';
            html += '<img src="' + s.immagine + '" alt="' + s.nome + '">';
            html += '<p><strong>' + s.nome + '</strong><br><span>' + s.ruolo + '</span></p>';
            html += '</div>';
        }
        html += '</div>';

        html += '<h3><i class="fas fa-project-diagram me-2"></i>Relazioni</h3>';
        html += '<div class="griglia">';
        for (let r of manga.relazioni) {
            let paginaRelazione = "";
            if (r.tipo === "MANGA") {
                paginaRelazione = "dettagli_manga.php";
            } else {
                paginaRelazione = "dettagli_anime.php";
            }

            html += '<div class="relazione">';
            html += '<a href="' + paginaRelazione + '?id=' + r.id + '">';
            if (r.immagine) {
                html += '<img src="' + r.immagine + '" alt="' + r.titolo + '">';
            }
            html += '<p>' + r.relazione + ' - ' + r.tipo + ': ' + r.titolo + '</p>';
            html += '</a>';
            html += '</div>';
        }
        html += '</div>';

        html += '<h3><i class="fas fa-thumbs-up me-2"></i>Raccomandazioni</h3>';
        html += '<div class="griglia">';
        for (let rec of manga.raccomandazioni) {
            html += '<div class="raccomandazione">';
            html += '<a href="dettagli_manga.php?id=' + rec.id + '">';
            html += '<img src="' + rec.immagine + '" alt="' + rec.titolo + '">';
            html += '<p>' + rec.titolo + '</p>';
            html += '</a>';
            html += '</div>';
        }
        html += '</div>';

        if (manga.trailer) {
            html += '<h3><i class="fas fa-play-circle me-2"></i>Trailer</h3>';
            html += '<div class="trailer-container"><iframe src="https://www.youtube.com/embed/' + manga.trailer.id + '" frameborder="0" allowfullscreen></iframe></div>';
        }

        document.getElementById("contenuto").innerHTML = html;
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

    async function salvaAttivita() {
        let status = document.getElementById('status').value;
        let punteggio = document.getElementById('punteggio').value;
        let capitoli_letti = document.getElementById('capitoli_letti').value;
        let volumi_letti = document.getElementById('volumi_letti').value;
        let startDate = document.getElementById('start_date').value;
        let endDate = document.getElementById('end_date').value;
        let note = document.getElementById('note').value;
        let reread = document.getElementById('rilettura').value;
        let preferito = 0;
        
        if (document.getElementById('preferito').checked) {
            preferito = 1;
        }

        if (punteggio !== "" && (punteggio < 0 || punteggio > 10)) {
            showAlert("Il punteggio deve essere compreso tra 0 e 10", "danger");
            return;
        }

        if (capitoli_letti < 0) {
            showAlert("Il numero di capitoli letti non può essere negativo", "danger");
            return;
        }

        let url = "ajax/attivita_manga.php?";
        url += "&manga_id=" + <?= $id ?>;
        url += "&status=" + status;
        url += "&punteggio=" + punteggio;
        url += "&capitoli_letti=" + capitoli_letti;
        url += "&volumi_letti=" + volumi_letti;
        url += "&start_date=" + startDate;
        url += "&end_date=" + endDate;
        url += "&note=" + note;
        url += "&reread=" + reread;
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

    document.addEventListener("DOMContentLoaded", async function () {
        await caricaDettagliManga(<?= $id ?>);
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
                <li class="breadcrumb-item active" aria-current="page">Dettagli Manga</li>
            </ol>
        </nav>
        
        <div id="contenuto">
            <!-- Loading state -->
            <div class="d-flex justify-content-center loading-state">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Caricamento in corso...</span>
                </div>
                <p class="ms-3">Caricamento dettagli manga...</p>
            </div>
            
            <!-- Content will be loaded here via JavaScript -->
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
                        <option value="Reading">Reading</option>
                        <option value="Complete">Complete</option>
                        <option value="Planning" selected>Planning</option>
                        <option value="Paused">Paused</option>
                        <option value="Dropped">Dropped</option>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label for="punteggio" class="form-label">Punteggio:</label>
                    <input type="number" id="punteggio" class="form-control" step="0.1" min="0" max="10" placeholder="Da 0 a 10">
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label for="capitoli_letti" class="form-label">Capitoli letti:</label>
                    <input type="number" id="capitoli_letti" class="form-control" min="0">
                </div>
                
                <div class="col-md-6">
                    <label for="volumi_letti" class="form-label">Volumi letti:</label>
                    <input type="number" id="volumi_letti" class="form-control" min="0">
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label for="rilettura" class="form-label">Riletture (quante volte):</label>
                    <input type="number" id="rilettura" class="form-control" min="0">
                </div>
                
                <div class="col-md-6">
                    <div class="form-check mt-4">
                        <input type="checkbox" id="preferito" class="form-check-input">
                        <label for="preferito" class="form-check-label">Aggiungi ai preferiti</label>
                    </div>
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