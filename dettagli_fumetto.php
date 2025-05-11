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
    <title>Dettagli Fumetto</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome per le icone -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <link rel="stylesheet" href="css/fumetto.css">    

<script>
    const paginaDettaglio = "dettagli_fumetto.php";
    
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

    async function caricaDettagliFumetto(id) {
        let url = "ajax/getFumettoByID.php?id=" + id;

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

        let fumetto = datiRicevuti["data"];
        let html = "";

        // Immagine e titolo principale
        if (fumetto.immagine && fumetto.immagine !== "") {
            html += '<img src="' + fumetto.immagine + '" alt="' + fumetto.titolo + '" class="fumetto-cover">';
        }
        html += '<h2>' + fumetto.titolo + '</h2>';
        
        // Dettagli principali
        html += '<p><strong>Descrizione:</strong> ' + fumetto.descrizione + '</p>';
        
        html += '<div class="row fumetto-stats">' +
                '<div class="col-md-4"><p><strong><i class="fas fa-book me-2"></i>Volume:</strong> ' + fumetto.volume + '</p></div>' +
                '<div class="col-md-4"><p><strong><i class="fas fa-hashtag me-2"></i>Numero:</strong> ' + fumetto.numero + '</p></div>' +
                '<div class="col-md-4"><p><strong><i class="fas fa-calendar me-2"></i>Data pubblicazione:</strong> ' + fumetto.data_pubblicazione + '</p></div>' +
                '</div>';
        
        // Alias
        html += '<h3><i class="fas fa-tag me-2"></i>Alias</h3>';
        if (fumetto.aliases && fumetto.aliases.trim() !== "") {
            let aliasesArray = fumetto.aliases.split("\n");
            html += '<ul class="aliases-list">';
            for (let alias of aliasesArray) {
                html += '<li>' + alias + '</li>';
            }
            html += '</ul>';
        } else {
            html += '<p>Nessun alias disponibile</p>';
        }

        // Autori
        if (fumetto.autori && fumetto.autori.length > 0) {
            html += '<h3><i class="fas fa-pen-fancy me-2"></i>Autori</h3>';
            html += '<div class="griglia">';
            for (let autore of fumetto.autori) {
                html += '<div class="staff-membro">';
                if (autore.immagine && autore.immagine !== "") {
                    html += '<img src="' + autore.immagine + '" alt="' + autore.nome + '">';
                }
                html += '<p><strong>' + autore.nome + '</strong>';
                if (autore.ruolo) {
                    html += '<br><span>' + autore.ruolo + '</span>';
                }
                html += '</p>';
                html += '</div>';
            }
            html += '</div>';
        }

        // Personaggi
        if (fumetto.personaggi && fumetto.personaggi.length > 0) {
            html += '<h3><i class="fas fa-users me-2"></i>Personaggi</h3>';
            html += '<div class="griglia">';
            for (let personaggio of fumetto.personaggi) {
                html += '<div class="personaggio">';
                if (personaggio.immagine && personaggio.immagine !== "") {
                    html += '<img src="' + personaggio.immagine + '" alt="' + personaggio.nome + '">';
                }
                html += '<p>' + personaggio.nome + '</p>';
                html += '</div>';
            }
            html += '</div>';
        }

        // Teams
        if (fumetto.teams && fumetto.teams.length > 0) {
            html += '<h3><i class="fas fa-users-gear me-2"></i>Teams</h3>';
            html += '<div class="griglia">';
            for (let team of fumetto.teams) {
                html += '<div class="relazione">';
                if (team.id) {
                    html += '<a href="' + paginaDettaglio + '?id=' + team.id + '">';
                }
                if (team.immagine && team.immagine !== "") {
                    html += '<img src="' + team.immagine + '" alt="' + team.nome + '">';
                }
                html += '<p>' + team.nome + '</p>';
                if (team.id) {
                    html += '</a>';
                }
                html += '</div>';
            }
            html += '</div>';
        }

        // Locations
        if (fumetto.locations && fumetto.locations.length > 0) {
            html += '<h3><i class="fas fa-map-location-dot me-2"></i>Locations</h3>';
            html += '<div class="griglia">';
            for (let location of fumetto.locations) {
                html += '<div class="relazione">';
                if (location.id) {
                    html += '<a href="' + paginaDettaglio + '?id=' + location.id + '">';
                }
                if (location.immagine && location.immagine !== "") {
                    html += '<img src="' + location.immagine + '" alt="' + location.nome + '">';
                }
                html += '<p>' + location.nome + '</p>';
                if (location.id) {
                    html += '</a>';
                }
                html += '</div>';
            }
            html += '</div>';
        }
        
        // Concepts
        if (fumetto.concepts && fumetto.concepts.length > 0) {
            html += '<h3><i class="fas fa-lightbulb me-2"></i>Concepts</h3>';
            html += '<div class="griglia">';
            for (let concept of fumetto.concepts) {
                html += '<div class="relazione">';
                if (concept.id) {
                    html += '<a href="' + paginaDettaglio + '?id=' + concept.id + '">';
                }
                if (concept.immagine && concept.immagine !== "") {
                    html += '<img src="' + concept.immagine + '" alt="' + concept.nome + '">';
                }
                html += '<p>' + concept.nome + '</p>';
                if (concept.id) {
                    html += '</a>';
                }
                html += '</div>';
            }
            html += '</div>';
        }
        
        // Objects
        if (fumetto.objects && fumetto.objects.length > 0) {
            html += '<h3><i class="fas fa-cube me-2"></i>Objects</h3>';
            html += '<div class="griglia">';
            for (let object of fumetto.objects) {
                html += '<div class="relazione">';
                if (object.id) {
                    html += '<a href="' + paginaDettaglio + '?id=' + object.id + '">';
                }
                if (object.immagine && object.immagine !== "") {
                    html += '<img src="' + object.immagine + '" alt="' + object.nome + '">';
                }
                html += '<p>' + object.nome + '</p>';
                if (object.id) {
                    html += '</a>';
                }
                html += '</div>';
            }
            html += '</div>';
        }

        document.getElementById("contenuto").innerHTML = html;
    }

    async function salvaAttivita() {
        let status = document.getElementById('status').value;
        let punteggio = document.getElementById('punteggio').value;
        let pagine_lette = document.getElementById('pagine_lette').value;
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

        if (pagine_lette < 0) {
            showAlert("Il numero di pagine lette non può essere negativo", "danger");
            return;
        }

        let url = "ajax/attivita_fumetto.php?";
        url += "&fumetto_id=" + <?= $id ?>;
        url += "&status=" + status;
        url += "&punteggio=" + punteggio;
        url += "&pagine_lette=" + pagine_lette;
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
        await caricaDettagliFumetto(<?= $id ?>);
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
                <li class="breadcrumb-item active" aria-current="page">Dettagli Fumetto</li>
            </ol>
        </nav>
        
        <div id="contenuto">
            <!-- Loading state -->
            <div class="d-flex justify-content-center loading-state">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Caricamento in corso...</span>
                </div>
                <p class="ms-3">Caricamento dettagli fumetto...</p>
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
                    <label for="pagine_lette" class="form-label">Pagine lette:</label>
                    <input type="number" id="pagine_lette" class="form-control" min="0">
                </div>
                
                <div class="col-md-6">
                    <label for="rilettura" class="form-label">Riletture (quante volte):</label>
                    <input type="number" id="rilettura" class="form-control" min="0">
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