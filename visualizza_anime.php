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
    <title>Attività Anime</title>
    <link rel="stylesheet" href="CSS/attivita_style.css">
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Verifica che l'editor esista
            if (!document.getElementById('editor')) {
                console.error("Editor non trovato nel DOM all'avvio della pagina!");
                // Crea l'elemento editor se non esiste
                createEditorIfNotExists();
            }
            
            caricaAttivita();

            // Ordina quando si cambia il valore nel select
            document.getElementById("ordinamento-select").addEventListener("change", function () {
                caricaAttivita();
            });
        });
        
        // Funzione per creare l'editor se non esiste
        function createEditorIfNotExists() {
            if (!document.getElementById('editor')) {
                console.log("Creazione elemento editor mancante");
                const editorDiv = document.createElement('div');
                editorDiv.id = 'editor';
                editorDiv.className = 'editor';
                editorDiv.style.display = 'none';
                
                editorDiv.innerHTML = `
                    <h3>Salva Attività Anime</h3>

                    <label for="status">Status:</label>
                    <select id="status">
                        <option value="Watching">Watching</option>
                        <option value="Complete">Complete</option>
                        <option value="Paused">Paused</option>
                        <option value="Dropped">Dropped</option>
                        <option value="Planning">Planning</option>
                    </select>

                    <label for="punteggio">Punteggio:</label>
                    <input type="number" id="punteggio" step="0.1" min="0" max="10">

                    <label for="episodi_visti">Episodi Visti:</label>
                    <input type="number" id="episodi_visti" min="0">

                    <label for="start_date">Data inizio:</label>
                    <input type="date" id="start_date">

                    <label for="end_date">Data fine:</label>
                    <input type="date" id="end_date">

                    <label for="note">Note:</label>
                    <textarea id="note"></textarea>

                    <label for="rewatch">Rewatch (quante volte):</label>
                    <input type="number" id="rewatch" min="0">

                    <div class="checkbox-container">
                        <label for="preferito">Preferito:</label>
                        <input type="checkbox" id="preferito">
                    </div>

                    <div class="buttons-container">
                        <button onclick="salvaAttivita()">Salva</button>
                    </div>
                `;
                
                // Aggiungi l'editor al container
                const container = document.querySelector('.container');
                if (container) {
                    container.appendChild(editorDiv);
                } else {
                    // Se non troviamo il container, aggiungiamolo al body come fallback
                    document.body.appendChild(editorDiv);
                }
            }
        }

        // Variabile per tenere traccia dell'attività corrente in fase di modifica
        let currentEditingItem = null;

        async function caricaAttivita() {
            let contenitore = document.getElementById("sezione_attivita");
            contenitore.innerHTML = "";

            let response = await fetch("ajax/carica_attivita.php");
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

            const ordineStatus = ["Watching", "Complete", "Paused", "Dropped", "Planning"];
            let attivitàPerStatus = {};
            for (let status of ordineStatus) {
                attivitàPerStatus[status] = [];
            }

            // Traccia gli ID già aggiunti per evitare duplicati (utilizzo di un array semplice)
            let idsAggiunti = [];

            for (let item of attività) {
                if ("episodi_visti" in item && ordineStatus.includes(item.status)) {
                    // Verifica se l'attività è già stata aggiunta
                    let itemId = item.riferimento_api;
                    if (!idsAggiunti.includes(itemId)) {
                        attivitàPerStatus[item.status].push(item);
                        idsAggiunti.push(itemId);
                    }
                }
            }

            let ordinamento = document.getElementById("ordinamento-select").value;

            function ordinaAttività(a, b) {
                if (ordinamento === "nome") {
                    return a.titolo.localeCompare(b.titolo);
                } else if (ordinamento === "punteggio") {
                    return b.punteggio - a.punteggio;
                } else if (ordinamento === "anno") {
                    return b.anno_uscita - a.anno_uscita;
                }
            }

            for (let status of ordineStatus) {
                if (attivitàPerStatus[status].length > 0) {
                    attivitàPerStatus[status].sort(ordinaAttività);

                    let intestazione = document.createElement("h2");
                    intestazione.textContent = status;
                    contenitore.appendChild(intestazione);

                    for (let attivitàItem of attivitàPerStatus[status]) {
                        let riga = document.createElement("div");
                        riga.className = "attivita-item";
                        riga.dataset.riferimentoApi = attivitàItem.riferimento_api;  // Salva riferimento_api nell'elemento

                        let username = attivitàItem["username"];
                        let titolo = attivitàItem["titolo"];
                        let riferimento_api = attivitàItem["riferimento_api"];
                        let episodi = attivitàItem["episodi_visti"];
                        let anno = attivitàItem["anno_uscita"];
                        let formato = attivitàItem["formato"];
                        let immagine = attivitàItem["immagine"];

                        let descrizione = "<strong>" + titolo + "</strong><br>Episodi visti: " + episodi +
                            "<br>Anno: " + anno + " - Formato: " + formato;

                        if (immagine) {
                            if (attivitàItem.note && attivitàItem.note.trim() !== "") {
                                descrizione += "<br>" +
                                    "<div class='tooltip-container'>" +
                                    "<img src='" + immagine + "' alt='" + titolo + "' style='width:100px;'>" +
                                    "<div class='tooltip-text'>" + attivitàItem.note.replaceAll("\n", "<br>") + "</div>" +
                                    "</div>";
                            } else {
                                descrizione += "<br><img src='" + immagine + "' alt='" + titolo + "' style='width:100px;'>";
                            }
                        }

                        riga.innerHTML = descrizione;
                        let bottoneModifica = document.createElement("button");
                        bottoneModifica.textContent = "Modifica";
                        bottoneModifica.onclick = function () {
                            toggleEditor(attivitàItem);
                        };
                        riga.appendChild(bottoneModifica);
                        contenitore.appendChild(riga);
                    }
                }
            }

            // Se c'era un'attività in modifica, riapri l'editor con i dati aggiornati
            if (currentEditingItem) {
                let riferimento_api = currentEditingItem.riferimento_api;
                let updatedData = await getUpdatedAnimeData(riferimento_api);
                if (updatedData) {
                    // Assicurati che l'editor esista
                    createEditorIfNotExists();
                    
                    // Trova l'elemento DOM dell'attività aggiornata
                    let items = document.querySelectorAll('.attivita-item');
                    for (let item of items) {
                        if (item.dataset.riferimentoApi === riferimento_api) {
                            // Assicurati che l'editor esista
                            let editor = document.getElementById('editor');
                            if (!editor) {
                                console.warn("Editor non trovato nel DOM anche dopo il tentativo di creazione");
                                return;
                            }
                            
                            // Aggiorna i dati correnti
                            currentEditingItem = updatedData;
                            // Visualizza l'editor nell'elemento corretto
                            item.appendChild(editor);
                            
                            // Imposta i valori nell'editor solo dopo averlo spostato nel DOM
                            setTimeout(() => {
                                populateEditor(updatedData);
                                editor.style.display = 'block';
                            }, 10);
                            break;
                        }
                    }
                }
            }
        }

        // Funzione per popolare l'editor con i dati
        function populateEditor(dati) {
        // Funzione di utilità per impostare in sicurezza i valori
        function setElementValue(id, value) {
            const element = document.getElementById(id);
            if (element) {
                element.value = value;
            } else {
                console.warn("Elemento con id '" + id + "' non trovato");
            }
        }
    
        // Funzione di utilità per impostare checkbox
            function setElementChecked(id, checked) {
                const element = document.getElementById(id);
                if (element) {
                    element.checked = checked;
                } else {
                    console.warn("Elemento con id '" + id + "' non trovato");
                }
            }
            
            // Imposta status
            if (dati.status != null) {
                setElementValue('status', dati.status);
            } else {
                setElementValue('status', "Planning");
            }

            // Imposta punteggio
            if (dati.punteggio != null) {
                setElementValue('punteggio', dati.punteggio);
            } else {
                setElementValue('punteggio', "");
            }

            // Imposta episodi visti
            if (dati.episodi_visti != null) {
                setElementValue('episodi_visti', dati.episodi_visti);
            } else {
                setElementValue('episodi_visti', "");
            }

            // Imposta data inizio
            if (dati.data_inizio != null) {
                setElementValue('start_date', dati.data_inizio);
            } else {
                setElementValue('start_date', "");
            }

            // Imposta data fine
            if (dati.data_fine != null) {
                setElementValue('end_date', dati.data_fine);
            } else {
                setElementValue('end_date', "");
            }

            // Imposta note
            if (dati.note != null) {
                setElementValue('note', dati.note);
            } else {
                setElementValue('note', "");
            }

            // Imposta rewatch
            if (dati.rewatch != null) {
                setElementValue('rewatch', dati.rewatch);
            } else {
                setElementValue('rewatch', "0");
            }

            // Imposta preferito
            if (dati.preferito == 1) {
                setElementChecked('preferito', true);
            } else {
                setElementChecked('preferito', false);
            }

            // Imposta riferimento API nel contenitore
            const contenuto = document.querySelector('#contenuto');
            if (contenuto) {
                contenuto.dataset.riferimentoApi = dati.riferimento_api;
            }

            // Imposta titolo nell'editor
            const editorTitle = document.querySelector('#editor h3');
            if (editorTitle) {
                editorTitle.textContent = "Modifica attività per: " + dati.titolo;
            }
        }

        // Funzione per il pannello di editor
        function toggleEditor(dati) {
            // Assicurati che l'editor esista
            createEditorIfNotExists();
            
            let editor = document.getElementById('editor');
            
            // Verifica se l'editor esiste dopo averlo creato
            if (!editor) {
                console.error("Impossibile trovare o creare l'editor nel DOM");
                return;
            }

            if (dati === undefined) {
                editor.style.display = 'none';
                currentEditingItem = null;
                return;
            }

            // Memorizza l'attività corrente
            currentEditingItem = dati;
            
            // Sposta l'editor accanto all'attività
            let attivitàItem = event.target.parentElement;
            attivitàItem.appendChild(editor);
            
            // Popola l'editor con i dati dopo averlo spostato nel DOM
            setTimeout(() => {
                populateEditor(dati);
                editor.style.display = 'block';
            }, 10);
        }


        // Funzione per salvare l'attività
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

            let riferimento_api = document.querySelector('#contenuto').dataset.riferimentoApi;

            let url = "ajax/attivita_anime.php?";
            url += "&anime_id=" + riferimento_api;
            url += "&status=" + status;
            url += "&punteggio=" + punteggio;
            url += "&episodi_visti=" + episodiVisti;
            url += "&start_date=" + startDate;
            url += "&end_date=" + endDate;
            url += "&note=" + note;
            url += "&rewatch=" + rewatch;
            url += "&preferito=" + preferito;

            let response = await fetch(url);
            let data = await response.json();

            if (data.status == "OK") {
                alert("Attività salvata con successo!");
                
                // Ricarica le attività dopo il salvataggio ma mantieni aperto l'editor
                await caricaAttivita(); 
                
                // L'editor verrà mantenuto aperto nella funzione caricaAttivita grazie alla variabile currentEditingItem
            } else {
                alert("Errore nel salvataggio dell'attività: " + data.message);
            }
        }

        // Funzione per ottenere i dati aggiornati di un anime specifico
        async function getUpdatedAnimeData(riferimento_api) {
            let response = await fetch("ajax/carica_attivita.php");
            if (!response.ok) {
                console.error("Errore nella fetch delle attività");
                return null;
            }

            let txt = await response.text();
            let datiRicevuti = JSON.parse(txt);

            if (datiRicevuti["status"] === "ERR") {
                console.error(datiRicevuti["msg"]);
                return null;
            }

            let attività = datiRicevuti["data"];
            
            // Trova l'attività con lo stesso riferimento_api
            for (let item of attività) {
                if (item.riferimento_api === riferimento_api) {
                    return item;
                }
            }
            return null;
        }
    </script>
</head>
<body>
    <div class="container">
        <!-- Sezione attività -->
        <div id="sezione_attivita">
            <!-- Qui verranno caricate le attività -->
        </div>

        <div id="ordinamento" style="margin: 20px 0;">
            <label for="ordinamento-select">Ordina per:</label>
            <select id="ordinamento-select">
                <option value="nome">Nome</option>
                <option value="punteggio">Punteggio</option>
                <option value="anno">Anno di uscita</option>
            </select>
        </div>

        <!-- Pannello editor -->
        <div id="editor" class="editor" style="display: none;">
            <h3>Salva Attività Anime</h3>

            <label for="status">Status:</label>
            <select id="status">
                <option value="Watching">Watching</option>
                <option value="Complete">Complete</option>
                <option value="Paused">Paused</option>
                <option value="Dropped">Dropped</option>
                <option value="Planning">Planning</option>
            </select>

            <label for="punteggio">Punteggio:</label>
            <input type="number" id="punteggio" step="0.1" min="0" max="10">

            <label for="episodi_visti">Episodi Visti:</label>
            <input type="number" id="episodi_visti" min="0">

            <label for="start_date">Data inizio:</label>
            <input type="date" id="start_date">

            <label for="end_date">Data fine:</label>
            <input type="date" id="end_date">

            <label for="note">Note:</label>
            <textarea id="note"></textarea>

            <label for="rewatch">Rewatch (quante volte):</label>
            <input type="number" id="rewatch" min="0">

            <div class="checkbox-container">
                <label for="preferito">Preferito:</label>
                <input type="checkbox" id="preferito">
            </div>

            <div class="buttons-container">
                <button onclick="salvaAttivita()">Salva</button>
            </div>
        </div>
    </div>
</body>
</html>