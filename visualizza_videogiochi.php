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
    <title>Attività Videogiochi</title>
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
                    <h3>Modifica attività videogioco</h3>

                    <label for="status">Status:</label>
                    <select id="status">
                        <option value="Playing">Playing</option>
                        <option value="Complete">Complete</option>
                        <option value="Planning">Planning</option>
                        <option value="Paused">Paused</option>
                        <option value="Dropped">Dropped</option>
                    </select>

                    <label for="punteggio">Punteggio:</label>
                    <input type="number" id="punteggio" step="0.1" min="0" max="10">

                    <label for="ore_giocate">Ore giocate:</label>
                    <input type="number" id="ore_giocate" min="0">

                    <label for="rigiocato">Rigiocato:</label>
                    <input type="number" id="rigiocato" min="0">

                    <label for="start_date">Data inizio:</label>
                    <input type="date" id="start_date">

                    <label for="end_date">Data fine:</label>
                    <input type="date" id="end_date">

                    <label for="note">Note:</label>
                    <textarea id="note"></textarea>

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

            let response = await fetch("ajax/carica_attivita_videogame.php");
            if (!response.ok) {
                console.error("Errore nella fetch delle attività videogiochi");
                return;
            }

            let txt = await response.text();
            let datiRicevuti = JSON.parse(txt);

            if (datiRicevuti["status"] === "ERR") {
                console.error(datiRicevuti["msg"]);
                return;
            }

            let attivita = datiRicevuti["data"];
            const ordineStatus = ["Playing", "Complete", "Paused", "Dropped", "Planning"];
            let attivitaPerStatus = {};
            
            for (let status of ordineStatus) {
                attivitaPerStatus[status] = [];
            }
            
            // Traccia gli ID già aggiunti per evitare duplicati
            let idsAggiunti = [];

            for (let item of attivita) {
                if ("ore_giocate" in item && ordineStatus.includes(item.status)) {
                    // Verifica se l'attività è già stata aggiunta
                    let itemId = item.guid;
                    if (!idsAggiunti.includes(itemId)) {
                        attivitaPerStatus[item.status].push(item);
                        idsAggiunti.push(itemId);
                    }
                }
            }

            let ordinamento = document.getElementById("ordinamento-select").value;
            function ordinaAttivita(a, b) {
                if (ordinamento === "nome") {
                    return a.titolo.localeCompare(b.titolo);
                }
                if (ordinamento === "punteggio") {
                    return b.punteggio - a.punteggio;
                }
                if (ordinamento === "data") {
                    return new Date(b.data_uscita) - new Date(a.data_uscita);
                }
            }

            for (let status of ordineStatus) {
                if (attivitaPerStatus[status].length > 0) {
                    attivitaPerStatus[status].sort(ordinaAttivita);

                    let intestazione = document.createElement("h2");
                    intestazione.textContent = status;
                    contenitore.appendChild(intestazione);

                    for (let attivitaItem of attivitaPerStatus[status]) {
                        let riga = document.createElement("div");
                        riga.className = "attivita-item";
                        riga.dataset.guid = attivitaItem.guid;  // Salva guid nell'elemento

                        let descrizione = "<strong>" + attivitaItem.titolo + "</strong><br>";
                        descrizione += "Ore giocate: " + attivitaItem.ore_giocate + " | Rigiocato: " + attivitaItem.rigiocato;
                        descrizione += "<br>Data uscita: " + attivitaItem.data_uscita;

                        if (attivitaItem.start_date) {
                            descrizione += "<br>Inizio: " + attivitaItem.start_date;
                        }

                        if (attivitaItem.end_date) {
                            descrizione += " | Fine: " + attivitaItem.end_date;
                        }

                        if (attivitaItem.immagine) {
                            if (attivitaItem.note && attivitaItem.note.trim() !== "") {
                                descrizione += "<br>" +
                                    "<div class='tooltip-container'>" +
                                    "<img src='" + attivitaItem.immagine + "' alt='" + attivitaItem.titolo + "' style='width:100px;'>" +
                                    "<div class='tooltip-text'>" + attivitaItem.note.replaceAll("\n", "<br>") + "</div>" +
                                    "</div>";
                            } else {
                                descrizione += "<br><img src='" + attivitaItem.immagine + "' alt='" + attivitaItem.titolo + "' style='width:100px;'>";
                            }
                        }

                        riga.innerHTML = descrizione;
                        let bottoneModifica = document.createElement("button");
                        bottoneModifica.textContent = "Modifica";
                        bottoneModifica.onclick = function () {
                            toggleEditor(attivitaItem);
                        };
                        riga.appendChild(bottoneModifica);
                        contenitore.appendChild(riga);
                    }
                }
            }
            
            // Se c'era un'attività in modifica, riapri l'editor con i dati aggiornati
            if (currentEditingItem) {
                let guid = currentEditingItem.guid;
                let updatedData = await getUpdatedVideogameData(guid);
                if (updatedData) {
                    // Assicurati che l'editor esista
                    createEditorIfNotExists();
                    
                    // Trova l'elemento DOM dell'attività aggiornata
                    let items = document.querySelectorAll('.attivita-item');
                    for (let item of items) {
                        if (item.dataset.guid === guid) {
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

            // Imposta ore giocate
            if (dati.ore_giocate != null) {
                setElementValue('ore_giocate', dati.ore_giocate);
            } else {
                setElementValue('ore_giocate', "");
            }

            // Imposta rigiocato
            if (dati.rigiocato != null) {
                setElementValue('rigiocato', dati.rigiocato);
            } else {
                setElementValue('rigiocato', "0");
            }

            // Imposta data inizio
            if (dati.start_date != null) {
                setElementValue('start_date', dati.start_date);
            } else {
                setElementValue('start_date', "");
            }

            // Imposta data fine
            if (dati.end_date != null) {
                setElementValue('end_date', dati.end_date);
            } else {
                setElementValue('end_date', "");
            }

            // Imposta note
            if (dati.note != null) {
                setElementValue('note', dati.note);
            } else {
                setElementValue('note', "");
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
                contenuto.dataset.riferimentoApi = dati.guid;
            }

            // Imposta titolo nell'editor
            const editorTitle = document.querySelector('#editor h3');
            if (editorTitle) {
                editorTitle.textContent = "Modifica attività per: " + dati.titolo;
            }
        }

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
            let attivitaItem = event.target.parentElement;
            attivitaItem.appendChild(editor);
            
            // Popola l'editor con i dati dopo averlo spostato nel DOM
            setTimeout(() => {
                populateEditor(dati);
                editor.style.display = 'block';
            }, 10);
        }

        async function salvaAttivita() {
            let status = document.getElementById('status').value;
            let punteggio = document.getElementById('punteggio').value;
            let ore_giocate = document.getElementById('ore_giocate').value;
            let rigiocato = document.getElementById('rigiocato').value;
            let startDate = document.getElementById('start_date').value;
            let endDate = document.getElementById('end_date').value;
            let note = document.getElementById('note').value;
            let preferito = 0;
            if (document.getElementById('preferito').checked) {
                preferito = 1;
            }

            let guid = currentEditingItem.guid;

            let url = "ajax/attivita_videogame.php?";
            url += "videogioco_guid=" + guid;
            url += "&status=" + status;
            url += "&punteggio=" + punteggio;
            url += "&ore_giocate=" + ore_giocate;
            url += "&rigiocato=" + rigiocato;
            url += "&start_date=" + startDate;
            url += "&end_date=" + endDate;
            url += "&note=" + note;
            url += "&preferito=" + preferito;

            let response = await fetch(url);
            let data = await response.json();

            if (data.status == "OK") {
                alert("Attività videogioco salvata con successo!");
                
                // Ricarica le attività dopo il salvataggio ma mantieni aperto l'editor
                await caricaAttivita();
                
                // L'editor verrà mantenuto aperto nella funzione caricaAttivita grazie alla variabile currentEditingItem
            } else {
                alert("Errore nel salvataggio dell'attività: " + data.message);
            }
        }

        // Funzione per ottenere i dati aggiornati di un videogioco specifico
        async function getUpdatedVideogameData(guid) {
            let response = await fetch("ajax/carica_attivita_videogame.php");
            if (!response.ok) {
                console.error("Errore nella fetch delle attività videogiochi");
                return null;
            }

            let txt = await response.text();
            let datiRicevuti = JSON.parse(txt);

            if (datiRicevuti["status"] === "ERR") {
                console.error(datiRicevuti["msg"]);
                return null;
            }

            let attivita = datiRicevuti["data"];
            
            // Trova l'attività con lo stesso guid
            for (let item of attivita) {
                if (item.guid === guid) {
                    return item;
                }
            }
            return null;
        }
    </script>
</head>
<body>
    <div class="container">

        <div id="sezione_attivita">
            <!-- Qui verranno caricate le attività -->
        </div>

        <div id="ordinamento" style="margin: 20px 0;">
            <label for="ordinamento-select">Ordina per:</label>
            <select id="ordinamento-select">
                <option value="nome">Nome</option>
                <option value="punteggio">Punteggio</option>
                <option value="data">Data Uscita</option>
            </select>
        </div>

        <!-- Pannello editor -->
        <div id="editor" class="editor" style="display: none;">
            <h3>Modifica attività videogioco</h3>

            <label for="status">Status:</label>
            <select id="status">
                <option value="Playing">Playing</option>
                <option value="Complete">Complete</option>
                <option value="Planning">Planning</option>
                <option value="Paused">Paused</option>
                <option value="Dropped">Dropped</option>
            </select>

            <label for="punteggio">Punteggio:</label>
            <input type="number" id="punteggio" step="0.1" min="0" max="10">

            <label for="ore_giocate">Ore giocate:</label>
            <input type="number" id="ore_giocate" min="0">

            <label for="rigiocato">Rigiocato:</label>
            <input type="number" id="rigiocato" min="0">

            <label for="start_date">Data inizio:</label>
            <input type="date" id="start_date">

            <label for="end_date">Data fine:</label>
            <input type="date" id="end_date">

            <label for="note">Note:</label>
            <textarea id="note"></textarea>

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