// Funzioni di validazione
function validaUsername(username) {
    return username && username.length >= 3;
}

function validaEmail(email) {
    let emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
    return emailRegex.test(email);
}

function validaNomeCognome(str) {
    let nomeRegex = /^[a-zA-ZàèéìòùÀÈÉÌÒÙ\s]+$/;
    return nomeRegex.test(str);
}

function validaTelefono(phone) {
    let phoneRegex = /^[0-9]{8,15}$/;
    return phoneRegex.test(phone);
}

function validaPassword(password) {
    return password && password.length >= 8;
}

function validaDataNascita(data) {
    if (!data) return false;
    
    let anno = parseInt(data.substring(0, 4), 10);
    let annoCorrente = new Date().getFullYear();
    return anno >= 1900 && anno <= annoCorrente;
}

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

// Funzione per validare il form di registrazione
function validaForm(event) {
    let isValid = true;
    
    // Elementi del form
    let username = document.getElementById('username');
    let email = document.getElementById('email');
    let firstName = document.getElementById('first_name');
    let lastName = document.getElementById('last_name');
    let phone = document.getElementById('phone');
    let birthdate = document.getElementById('birthdate');
    let password = document.getElementById('password');
    let confermaPassword = document.getElementById('confermaPassword');
    
    // Reset dello stato di validazione
    let elementiForm = [username, email, firstName, lastName, phone, birthdate, password, confermaPassword];
    for (let i = 0; i < elementiForm.length; i++) {
        elementiForm[i].classList.remove('is-invalid');
        elementiForm[i].classList.remove('is-valid');
    }
    
    // Validazione username
    if (!validaUsername(username.value)) {
        username.classList.add('is-invalid');
        isValid = false;
    } else {
        username.classList.add('is-valid');
    }
    
    // Validazione email
    if (!validaEmail(email.value)) {
        email.classList.add('is-invalid');
        isValid = false;
    } else {
        email.classList.add('is-valid');
    }
    
    // Validazione nome
    if (!validaNomeCognome(firstName.value)) {
        firstName.classList.add('is-invalid');
        isValid = false;
    } else {
        firstName.classList.add('is-valid');
    }
    
    // Validazione cognome
    if (!validaNomeCognome(lastName.value)) {
        lastName.classList.add('is-invalid');
        isValid = false;
    } else {
        lastName.classList.add('is-valid');
    }
    
    // Validazione telefono
    if (!validaTelefono(phone.value)) {
        phone.classList.add('is-invalid');
        isValid = false;
    } else {
        phone.classList.add('is-valid');
    }
    
    // Validazione data di nascita
    if (!validaDataNascita(birthdate.value)) {
        birthdate.classList.add('is-invalid');
        isValid = false;
    } else {
        birthdate.classList.add('is-valid');
    }
    
    // Validazione password
    if (!validaPassword(password.value)) {
        password.classList.add('is-invalid');
        isValid = false;
    } else {
        password.classList.add('is-valid');
    }
    
    // Validazione conferma password
    if (password.value !== confermaPassword.value) {
        confermaPassword.classList.add('is-invalid');
        isValid = false;
    } else if (password.value) {
        confermaPassword.classList.add('is-valid');
    }
    
    // Controllo immagine profilo
    let fileInput = document.getElementById('profile_image');
    let defaultImage = document.getElementById('immagine_default');
    
    if (fileInput && defaultImage && fileInput.files.length === 0 && !defaultImage.value) {
        alert("Devi caricare un'immagine del profilo o scegliere un'immagine predefinita.");
        isValid = false;
    }
    
    // Impedisci l'invio del form se non è valido
    if (!isValid) {
        event.preventDefault();
        return false;
    }
    
    return true;
}

// Configurazione degli eventi al caricamento del DOM
document.addEventListener('DOMContentLoaded', function() {
    // Validazione in tempo reale per username
    let username = document.getElementById('username');
    if (username) {
        username.addEventListener('input', function() {
            if (this.value.length > 0) {
                if (validaUsername(this.value)) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    this.classList.remove('is-valid');
                    this.classList.add('is-invalid');
                }
            }
        });
    }
    
    // Validazione in tempo reale per nome
    let firstName = document.getElementById('first_name');
    if (firstName) {
        firstName.addEventListener('input', function() {
            if (this.value.length > 0) {
                if (validaNomeCognome(this.value)) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    this.classList.remove('is-valid');
                    this.classList.add('is-invalid');
                }
            }
        });
    }
    
    // Validazione in tempo reale per cognome
    let lastName = document.getElementById('last_name');
    if (lastName) {
        lastName.addEventListener('input', function() {
            if (this.value.length > 0) {
                if (validaNomeCognome(this.value)) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    this.classList.remove('is-valid');
                    this.classList.add('is-invalid');
                }
            }
        });
    }
    
    // Validazione in tempo reale per telefono
    let phone = document.getElementById('phone');
    if (phone) {
        phone.addEventListener('input', function() {
            if (this.value.length > 0) {
                if (validaTelefono(this.value)) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    this.classList.remove('is-valid');
                    this.classList.add('is-invalid');
                }
            }
        });
    }
    
    // Validazione in tempo reale per email
    let email = document.getElementById('email');
    if (email) {
        email.addEventListener('input', function() {
            if (this.value.length > 0) {
                if (validaEmail(this.value)) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    this.classList.remove('is-valid');
                    this.classList.add('is-invalid');
                }
            }
        });
    }
    
    // Validazione in tempo reale per data di nascita
    let birthdate = document.getElementById('birthdate');
    if (birthdate) {
        birthdate.addEventListener('input', function() {
            if (this.value.length > 0) {
                if (validaDataNascita(this.value)) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    this.classList.remove('is-valid');
                    this.classList.add('is-invalid');
                }
            }
        });
    }
    
    // Validazione in tempo reale per password
    let password = document.getElementById('password');
    if (password) {
        password.addEventListener('input', function() {
            if (this.value.length > 0) {
                if (validaPassword(this.value)) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    this.classList.remove('is-valid');
                    this.classList.add('is-invalid');
                }
            }
            
            // Controlla anche la conferma password
            let confermaPassword = document.getElementById('confermaPassword');
            if (confermaPassword && confermaPassword.value.length > 0) {
                if (this.value === confermaPassword.value) {
                    confermaPassword.classList.remove('is-invalid');
                    confermaPassword.classList.add('is-valid');
                } else {
                    confermaPassword.classList.remove('is-valid');
                    confermaPassword.classList.add('is-invalid');
                }
            }
        });
    }
    
    // Validazione in tempo reale per conferma password
    let confermaPassword = document.getElementById('confermaPassword');
    if (confermaPassword) {
        confermaPassword.addEventListener('input', function() {
            if (this.value.length > 0) {
                let passwordValue = document.getElementById('password').value;
                if (this.value === passwordValue) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    this.classList.remove('is-valid');
                    this.classList.add('is-invalid');
                }
            }
        });
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
                    } else {
                        // Se non esiste ancora l'anteprima, crea l'elemento
                        let previewSection = document.getElementById('preview-img');
                        if (previewSection) {
                            let img = document.createElement('img');
                            img.src = e.target.result;
                            img.id = 'preview-image';
                            img.className = 'preview-image';
                            img.style = 'width:60px; height:60px; border-radius:50%; margin-top:10px;';
                            previewSection.appendChild(img);
                        }
                    }
                    
                    // Reset dell'immagine predefinita
                    let defaultImage = document.getElementById('immagine_default');
                    if (defaultImage) {
                        defaultImage.value = '';
                    }
                    
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
});