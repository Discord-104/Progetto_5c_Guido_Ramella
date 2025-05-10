// Funzione per generare un timestamp unico
function getUniqueTimestamp() {
    let now = new Date();
    let year = now.getFullYear();
    let month = String(now.getMonth() + 1).padStart(2, '0');
    let day = String(now.getDate()).padStart(2, '0');
    let hours = String(now.getHours()).padStart(2, '0');
    let minutes = String(now.getMinutes()).padStart(2, '0');
    let seconds = String(now.getSeconds()).padStart(2, '0');
    let milliseconds = String(now.getMilliseconds()).padStart(3, '0');
    
    return year + month + day + "_" + hours + minutes + seconds + "_" + milliseconds;
}

// Gestione delle immagini del profilo
document.addEventListener('DOMContentLoaded', function() {
    // Seleziona un'immagine predefinita all'avvio
    let defaultImage = 'default_avatar.png'; // Immagine predefinita 
    
    // Imposta l'immagine predefinita nel campo nascosto
    let defaultImageInput = document.getElementById('immagine_default');
    if (defaultImageInput) {
        defaultImageInput.value = defaultImage;
    }
    
    // Gestione dell'upload di un'immagine personale
    let fileInput = document.getElementById('profile_image');
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                let file = this.files[0];
                
                // Controllo del tipo di file
                let validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
                if (!validTypes.includes(file.type)) {
                    alert('Formato file non supportato. Utilizzare solo immagini JPG, PNG o GIF.');
                    this.value = '';
                    return;
                }
                
                // Controllo della dimensione del file (max 2MB)
                let maxSize = 2 * 1024 * 1024; // 2MB
                if (file.size > maxSize) {
                    alert('L\'immagine è troppo grande. Dimensione massima consentita: 2MB.');
                    this.value = '';
                    return;
                }
                
                // Ottieni l'estensione del file
                let fileExt = file.name.split('.').pop().toLowerCase();
                
                // Crea un nuovo nome file con timestamp
                let timestamp = getUniqueTimestamp();
                let newFileName = 'user_' + timestamp + '.' + fileExt;
                
                // Aggiorna il campo nascosto per il nuovo nome del file
                let newFileNameInput = document.getElementById('new_file_name');
                if (newFileNameInput) {
                    newFileNameInput.value = newFileName;
                }
                
                // Anteprima dell'immagine
                let reader = new FileReader();
                reader.onload = function(e) {
                    // Aggiorna l'anteprima
                    let previewImage = document.getElementById('preview-image');
                    if (previewImage) {
                        previewImage.src = e.target.result;
                        
                        // Aggiungi effetto di animazione per evidenziare il cambio
                        previewImage.classList.add('preview-updated');
                        setTimeout(function() {
                            previewImage.classList.remove('preview-updated');
                        }, 500);
                    }
                    
                    // Reset dell'immagine predefinita
                    document.getElementById('immagine_default').value = '';
                    
                    // Rimuovi la classe 'selected' da tutti gli elementi
                    let imageItems = document.querySelectorAll('.profile-image-item');
                    for (let i = 0; i < imageItems.length; i++) {
                        imageItems[i].classList.remove('selected');
                    }
                    
                    // Mostra un messaggio di conferma
                    console.log("Immagine personalizzata caricata e pronta per l'invio");
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Gestione del click sugli elementi delle immagini predefinite
    let profileImageItems = document.querySelectorAll('.profile-image-item');
    if (profileImageItems) {
        for (let i = 0; i < profileImageItems.length; i++) {
            profileImageItems[i].addEventListener('click', function() {
                // Ottieni il valore dell'immagine selezionata
                let selectedImage = this.getAttribute('data-value');
                
                // Aggiorna il campo nascosto
                let defaultImageInput = document.getElementById('immagine_default');
                if (defaultImageInput) {
                    defaultImageInput.value = selectedImage;
                }
                
                // Rimuovi la classe 'selected' da tutti gli elementi
                for (let j = 0; j < profileImageItems.length; j++) {
                    profileImageItems[j].classList.remove('selected');
                }
                
                // Aggiungi la classe 'selected' all'elemento corrente
                this.classList.add('selected');
                
                // Aggiorna l'anteprima dell'immagine
                let previewImage = document.getElementById('preview-image');
                if (previewImage) {
                    previewImage.src = 'default_profiles/' + selectedImage;
                    
                    // Aggiungi effetto di animazione per evidenziare il cambio
                    previewImage.classList.add('preview-updated');
                    setTimeout(function() {
                        previewImage.classList.remove('preview-updated');
                    }, 500);
                    
                    // Mostra un messaggio di conferma
                    console.log("Immagine predefinita selezionata: " + selectedImage);
                }
                
                // Reset dell'input file
                let fileInput = document.getElementById('profile_image');
                if (fileInput) {
                    fileInput.value = '';
                }
            });
        }
    }
});