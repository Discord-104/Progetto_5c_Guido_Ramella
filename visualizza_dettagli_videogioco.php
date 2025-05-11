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
    <title>Visualizza Dettagli Estesi Videogioco - NerdVerse</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome per le icone -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <link rel="stylesheet" href="CSS/videogame.css">

<script>
    async function fetchDati(url, campo) {
        let response = await fetch(url);

        if (!response.ok) {
            console.error("Errore nella fetch di " + campo);
            return [];
        }

        let data = await response.text();
        console.log(campo + ":", data);

        let json = JSON.parse(data);

        if (json.status === "OK") {
            return json[campo];
        }

        console.warn(campo + " non disponibili");
        return [];
    }


    async function caricaDettagliVideogioco(guid) {
        let url = "ajax/videogame/getVideogamebyID.php?guid=" + guid;
        
        let response = await fetch(url);
        if (!response.ok) {
            throw new Error("Errore nella fetch.");
        }

        let dati = await response.text();
        let json = JSON.parse(dati);

        if (json.status === "ERR") {
            document.getElementById("dettagli").innerHTML = 
                '<div class="alert alert-danger" role="alert">' +
                '<i class="fas fa-exclamation-triangle me-2"></i>' +
                'Errore: ' + json.msg + '</div>';
            return null;
        }

        return json.dato;
    }

    function creaListaOggetti(titolo, array, tipo = "") {
        if (!array || array.length === 0) return "";
        
        let html = '<h3><i class="fas fa-' + getIconaPerTitolo(titolo) + ' me-2"></i>' + titolo + '</h3>';
        html += '<div class="griglia">';
        
        for (let item of array) {
            html += '<div class="personaggio">';
            if (item.immagine && item.immagine !== "N/D") {
                html += '<img src="' + item.immagine + '" alt="' + item.nome + '">';
            }
            
            if (tipo === "similar") {
                html += '<p><a href="dettagli_videogame.php?guid=' + item.guid + '" class="text-white text-decoration-none">' + item.nome + '</a></p>';
            } else {
                html += '<p>' + item.nome + '</p>';
            }
            
            html += '</div>';
        }
        
        html += '</div>';
        return html;
    }

    function getIconaPerTitolo(titolo) {
        switch (titolo) {
            case "Personaggi": return "users";
            case "Giochi simili": return "gamepad";
            case "Generi": return "tag";
            case "Piattaforme": return "desktop";
            case "Sviluppatori": return "code";
            case "Publisher": return "building";
            case "Temi": return "palette";
            case "DLC": return "puzzle-piece";
            case "Franchise": return "film";
            default: return "info-circle";
        }
    }

    function creaListaSemplice(titolo, array) {
        if (!array || array.length === 0) return "";
        
        let html = '<h3><i class="fas fa-' + getIconaPerTitolo(titolo) + ' me-2"></i>' + titolo + '</h3>';
        html += '<ul class="aliases-list">';
        
        for (let item of array) {
            html += '<li>' + item + '</li>';
        }
        
        html += '</ul>';
        return html;
    }

    document.addEventListener("DOMContentLoaded", async function () {
        function getQueryParam(nome) {
            let params = new URLSearchParams(window.location.search);
            return params.get(nome);
        }

        let guid = getQueryParam("guid");

        if (!guid) {
            document.getElementById("dettagli").innerHTML =
                '<div class="alert alert-danger" role="alert">' +
                '<i class="fas fa-exclamation-triangle me-2"></i>' +
                'GUID mancante nell\'URL.</div>';
            return;
        }

        let gioco = await caricaDettagliVideogioco(guid);

        if (!gioco) {
            document.getElementById("dettagli").innerHTML =
                '<div class="alert alert-warning" role="alert">' +
                '<i class="fas fa-exclamation-circle me-2"></i>' +
                'Dettagli del videogioco non disponibili.</div>';
            return;
        }

        let personaggi = await fetchDati("ajax/videogame/getCharacters.php?guid=" + guid, "characters");
        let simili = await fetchDati("ajax/videogame/getSimilarGames.php?guid=" + guid, "similar_games");

        renderizzaDettagliEstesi(gioco, personaggi, simili);
    });

    function renderizzaDettagliEstesi(gioco, personaggi, simili) {
        let html = "";

        // Header con immagine e titolo
        html += '<div class="mb-4">';
        if (gioco.immagine && gioco.immagine !== "N/D") {
            html += '<img src="' + gioco.immagine + '" alt="' + gioco.nome + '" class="immagine">';
        }
        html += '<h2>' + gioco.nome + '</h2>';
        
        // Stats principali
        html += '<div class="row videogame-stats">';
        if (gioco.data_uscita) {
            html += '<div class="col-md-6"><p><strong><i class="fas fa-calendar me-2"></i>Data di uscita:</strong> ' + gioco.data_uscita + '</p></div>';
        } else {
            html += '<div class="col-md-6"><p><strong><i class="fas fa-calendar me-2"></i>Data di uscita:</strong> N/D</p></div>';
        }
        html += '</div>';
        
        // Descrizione completa
        html += '<div class="mt-4">';
        html += '<p><strong>Descrizione:</strong> ' + gioco.descrizione + '</p>';
        html += '</div>';
        html += '</div>';
        
        // Personaggi del videogioco
        html += creaListaOggetti("Personaggi", personaggi);
        
        // Giochi simili
        html += creaListaOggetti("Giochi simili", simili, "similar");
        
        // Generi
        html += creaListaSemplice("Generi", gioco.generi);
        
        // Piattaforme
        html += creaListaSemplice("Piattaforme", gioco.piattaforme);
        
        // Developer
        html += creaListaSemplice("Sviluppatori", gioco.sviluppatori);
        
        // Publisher
        html += creaListaSemplice("Publisher", gioco.publisher);
        
        // Temi
        html += creaListaSemplice("Temi", gioco.temi);
        
        // DLC
        html += creaListaSemplice("DLC", gioco.dlc);
        
        // Franchise
        html += creaListaSemplice("Franchise", gioco.franchises);

        let params = new URLSearchParams(window.location.search);
        let guid = params.get("guid");

        // Link per tornare alla pagina principale del gioco
        html += '<div class="text-center mt-4">';
        html += '<a href="dettagli_videogame.php?guid=' + guid + '" class="btn btn-primary">';
        html += '<i class="fas fa-arrow-left me-2"></i>Torna ai dettagli semplici';
        html += '</a>';
        html += '</div>';

        document.getElementById("dettagli").innerHTML = html;
    }
</script>
</head>
<body>
    <!-- Parallax background effect -->
    <div class="parallax-bg"></div>
    
    <div class="container">
        <nav aria-label="breadcrumb" class="mt-2 mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="home.php" class="text-decoration-none"><i class="fas fa-home me-1"></i>Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Dettagli Estesi</li>
            </ol>
        </nav>
        
        <div id="dettagli">
            <!-- Loading state -->
            <div class="d-flex justify-content-center loading-state">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Caricamento in corso...</span>
                </div>
                <p class="ms-3">Caricamento dettagli estesi del videogioco...</p>
            </div>
            
            <!-- Content will be loaded here via JavaScript -->
        </div>
        
        <footer class="mt-5 text-center text-muted">
            <p><small>&copy; <?php echo date('Y'); ?> NerdVerse - Tutti i diritti riservati</small></p>
        </footer>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>