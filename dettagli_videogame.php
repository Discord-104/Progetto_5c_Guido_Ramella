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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dettagli Videogioco</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome per le icone -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/videogame.css">

<script>
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
        let ore_giocate = document.getElementById('ore_giocate').value;
        let startDate = document.getElementById('start_date').value;
        let endDate = document.getElementById('end_date').value;
        let note = document.getElementById('note').value;
        let rigiocato = document.getElementById('rigiocato').value;
        let preferito = 0;
        
        if (document.getElementById('preferito').checked) {
            preferito = 1;
        }

        if (punteggio !== "" && (punteggio < 0 || punteggio > 10)) {
            showAlert("Il punteggio deve essere compreso tra 0 e 10", "danger");
            return;
        }

        if (ore_giocate < 0) {
            showAlert("Il numero di ore giocate non può essere negativo", "danger");
            return;
        }

        let params = new URLSearchParams(window.location.search);
        let guid = params.get("guid");

        let url = "ajax/attivita_videogame.php?videogioco_guid=" + guid;
        url += "&status=" + status;
        url += "&punteggio=" + punteggio;
        url += "&ore_giocate=" + ore_giocate;
        url += "&start_date=" + startDate;
        url += "&end_date=" + endDate;
        url += "&note=" + note;
        url += "&rigiocato=" + rigiocato;
        url += "&preferito=" + preferito;

        let response = await fetch(url);
        
        if (response.ok) {
            let data = await response.json();
            
            if (data.status === "OK") {
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

    async function caricaDettagliVideogioco(guid) {
        let url = "ajax/videogame/getVideogamebyID.php?guid=" + guid;
        
        let response = await fetch(url);
        if (!response.ok) {
            throw new Error("Errore nella fetch.");
        }

        let txt = await response.text();
        let datiRicevuti = JSON.parse(txt);

        if (datiRicevuti["status"] === "ERR") {
            document.getElementById("dettagli").innerHTML = 
                '<div class="alert alert-danger" role="alert">' +
                '<i class="fas fa-exclamation-triangle me-2"></i>' +
                'Errore: ' + datiRicevuti["msg"] + '</div>';
            return;
        }

        let gioco = datiRicevuti["dato"];
        let html = "";

        // Immagine e titolo principale
        if (gioco.immagine && gioco.immagine !== "N/D") {
            html += '<img src="' + gioco.immagine + '" alt="' + gioco.nome + '" class="immagine">';
        }
        html += '<h2>' + gioco.nome + '</h2>';
        
        // Descrizione
        html += '<p><strong>Descrizione:</strong> ' + gioco.descrizione + '</p>';
        
        // Stats principali
        html += '<div class="row videogame-stats">';
        if (gioco.data_uscita) {
            html += '<div class="col-md-6"><p><strong><i class="fas fa-calendar me-2"></i>Data di uscita:</strong> ' + gioco.data_uscita + '</p></div>';
        } else {
            html += '<div class="col-md-6"><p><strong><i class="fas fa-calendar me-2"></i>Data di uscita:</strong> N/D</p></div>';
        }
        html += '</div>';
        
        // Generi
        if (gioco.generi && gioco.generi.length > 0) {
            html += '<h3><i class="fas fa-gamepad me-2"></i>Generi</h3>';
            html += '<ul class="aliases-list">';
            for (let genere of gioco.generi) {
                html += '<li>' + genere + '</li>';
            }
            html += '</ul>';
        }
        
        // Piattaforme
        if (gioco.piattaforme && gioco.piattaforme.length > 0) {
            html += '<h3><i class="fas fa-desktop me-2"></i>Piattaforme</h3>';
            html += '<ul class="aliases-list">';
            for (let piattaforma of gioco.piattaforme) {
                html += '<li>' + piattaforma + '</li>';
            }
            html += '</ul>';
        }
        
        // DLC
        if (gioco.dlc && gioco.dlc.length > 0) {
            html += '<h3><i class="fas fa-puzzle-piece me-2"></i>DLC</h3>';
            html += '<ul class="aliases-list">';
            for (let dlc of gioco.dlc) {
                html += '<li>' + dlc + '</li>';
            }
            html += '</ul>';
        }
        
        // Developer
        if (gioco.sviluppatori && gioco.sviluppatori.length > 0) {
            html += '<h3><i class="fas fa-code me-2"></i>Sviluppatori</h3>';
            html += '<ul class="aliases-list">';
            for (let sviluppatore of gioco.sviluppatori) {
                html += '<li>' + sviluppatore + '</li>';
            }
            html += '</ul>';
        }
        
        // Publisher
        if (gioco.publisher && gioco.publisher.length > 0) {
            html += '<h3><i class="fas fa-building me-2"></i>Publisher</h3>';
            html += '<ul class="aliases-list">';
            for (let pub of gioco.publisher) {
                html += '<li>' + pub + '</li>';
            }
            html += '</ul>';
        }
        
        // Temi
        if (gioco.temi && gioco.temi.length > 0) {
            html += '<h3><i class="fas fa-palette me-2"></i>Temi</h3>';
            html += '<ul class="aliases-list">';
            for (let tema of gioco.temi) {
                html += '<li>' + tema + '</li>';
            }
            html += '</ul>';
        }
        
        // Franchise
        if (gioco.franchises && gioco.franchises.length > 0) {
            html += '<h3><i class="fas fa-film me-2"></i>Franchise</h3>';
            html += '<ul class="aliases-list">';
            for (let franchise of gioco.franchises) {
                html += '<li>' + franchise + '</li>';
            }
            html += '</ul>';
        }
        
        // Aliases
        let aliasesArray = [];
        if (gioco.aliases) {
            aliasesArray = gioco.aliases.split("\n");
            if (aliasesArray.length > 0 && aliasesArray[0] !== "") {
                html += '<h3><i class="fas fa-tag me-2"></i>Alias</h3>';
                html += '<ul class="aliases-list">';
                for (let alias of aliasesArray) {
                    if (alias.trim() !== "") {
                        html += '<li>' + alias + '</li>';
                    }
                }
                html += '</ul>';
            }
        }
        
        // Bottone per visualizzare dettagli estesi
        html += '<div class="text-center mt-4">';
        html += '<a href="visualizza_dettagli_videogioco.php?guid=' + guid + '" class="btn btn-primary">';
        html += '<i class="fas fa-info-circle me-2"></i>Visualizza dettagli estesi';
        html += '</a>';
        html += '</div>';

        document.getElementById("dettagli").innerHTML = html;
    }

    document.addEventListener("DOMContentLoaded", async function () {
        let params = new URLSearchParams(window.location.search);
        let guid = params.get("guid");

        if (!guid) {
            document.getElementById("dettagli").innerHTML = 
                '<div class="alert alert-danger" role="alert">' +
                '<i class="fas fa-exclamation-triangle me-2"></i>' +
                'GUID mancante nell\'URL.</div>';
            return;
        }

        await caricaDettagliVideogioco(guid);
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
                <li class="breadcrumb-item active" aria-current="page">Dettagli Videogioco</li>
            </ol>
        </nav>
        
        <div id="dettagli">
            <!-- Loading state -->
            <div class="d-flex justify-content-center loading-state">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Caricamento in corso...</span>
                </div>
                <p class="ms-3">Caricamento dettagli videogioco...</p>
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
                        <option value="Playing">Playing</option>
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
                    <label for="ore_giocate" class="form-label">Ore giocate:</label>
                    <input type="number" id="ore_giocate" class="form-control" min="0">
                </div>
                
                <div class="col-md-6">
                    <label for="rigiocato" class="form-label">Rigiocato (quante volte):</label>
                    <input type="number" id="rigiocato" class="form-control" min="0">
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

            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="form-check mt-2">
                        <input type="checkbox" id="preferito" class="form-check-input">
                        <label for="preferito" class="form-check-label">Aggiungi ai preferiti</label>
                    </div>
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