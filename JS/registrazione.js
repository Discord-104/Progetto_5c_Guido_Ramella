// Funzioni di validazione
function validaEmail(email) {
    return /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/.test(email);
}

function validaNomeCognome(nome) {
    return /^[a-zA-ZàèéìòùÀÈÉÌÒÙ\s]+$/.test(nome);
}

function validaTelefono(telefono) {
    return /^[0-9]{8,15}$/.test(telefono);
}

function validaPassword(password) {
    return password.length >= 8;
}

function validaUsername(username) {
    return username.length >= 3;
}

function validaDataNascita(data) {
    let anno = parseInt(data.substring(0, 4));
    return anno >= 1900 && anno <= new Date().getFullYear();
}

// Funzione di validazione del form completo
function validateForm(event) {
    let isValid = true;
    let form = document.getElementById('profileForm');
    
    // Validazione username
    let username = document.getElementById('username');
    if (!username.value || !validaUsername(username.value)) {
        username.classList.add('is-invalid');
        isValid = false;
    } else {
        username.classList.remove('is-invalid');
        username.classList.add('is-valid');
    }
    
    // Validazione email
    let email = document.getElementById('email');
    if (!email.value || !validaEmail(email.value)) {
        email.classList.add('is-invalid');
        isValid = false;
    } else {
        email.classList.remove('is-invalid');
        email.classList.add('is-valid');
    }
    
    // Validazione nome
    let firstName = document.getElementById('first_name');
    if (!firstName.value || !validaNomeCognome(firstName.value)) {
        firstName.classList.add('is-invalid');
        isValid = false;
    } else {
        firstName.classList.remove('is-invalid');
        firstName.classList.add('is-valid');
    }
    
    // Validazione cognome
    let lastName = document.getElementById('last_name');
    if (!lastName.value || !validaNomeCognome(lastName.value)) {
        lastName.classList.add('is-invalid');
        isValid = false;
    } else {
        lastName.classList.remove('is-invalid');
        lastName.classList.add('is-valid');
    }
    
    // Validazione telefono
    let phone = document.getElementById('phone');
    if (!phone.value || !validaTelefono(phone.value)) {
        phone.classList.add('is-invalid');
        isValid = false;
    } else {
        phone.classList.remove('is-invalid');
        phone.classList.add('is-valid');
    }
    
    // Validazione data di nascita
    let birthdate = document.getElementById('birthdate');
    if (!birthdate.value || !validaDataNascita(birthdate.value)) {
        birthdate.classList.add('is-invalid');
        isValid = false;
    } else {
        birthdate.classList.remove('is-invalid');
        birthdate.classList.add('is-valid');
    }
    
    // Validazione password (solo se si sta cambiando la password)
    let currentPassword = document.getElementById('current_password');
    let newPassword = document.getElementById('new_password');
    let confirmPassword = document.getElementById('confirm_password');
    
    if (currentPassword.value || newPassword.value || confirmPassword.value) {
        if (!currentPassword.value) {
            currentPassword.classList.add('is-invalid');
            isValid = false;
        } else {
            currentPassword.classList.remove('is-invalid');
        }
        
        if (!newPassword.value || !validaPassword(newPassword.value)) {
            newPassword.classList.add('is-invalid');
            isValid = false;
        } else {
            newPassword.classList.remove('is-invalid');
            newPassword.classList.add('is-valid');
        }
        
        if (newPassword.value !== confirmPassword.value) {
            confirmPassword.classList.add('is-invalid');
            isValid = false;
        } else if (newPassword.value) {
            confirmPassword.classList.remove('is-invalid');
            confirmPassword.classList.add('is-valid');
        }
    }
    
    if (!isValid) {
        event.preventDefault();
        return false;
    }
    
    return true;
}

// Funzione per mostrare o nascondere feedback di validazione
function toggleValidationFeedback(element, isValid) {
    if (isValid) {
        element.classList.remove('is-invalid');
        element.classList.add('is-valid');
    } else {
        element.classList.add('is-invalid');
        element.classList.remove('is-valid');
    }
}

// Funzione per gestire l'anteprima dell'immagine
function handleImagePreview(event) {
    let file = event.target.files[0];
    if (file) {
        let reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-image').src = e.target.result;
            // Resetta il campo immagine predefinita
            document.getElementById('immagine_default').value = '';
            // Rimuovi la selezione dalle immagini predefinite
            let imageItems = document.querySelectorAll('.profile-image-item');
            for (let i = 0; i < imageItems.length; i++) {
                imageItems[i].classList.remove('selected');
            }
        };
        reader.readAsDataURL(file);
    }
}

// Gestione della selezione di un'immagine predefinita
function setupDefaultImageSelection() {
    let imageItems = document.querySelectorAll('.profile-image-item');
    for (let i = 0; i < imageItems.length; i++) {
        imageItems[i].addEventListener('click', function() {
            // Rimuovi la classe selected da tutti gli elementi
            let allItems = document.querySelectorAll('.profile-image-item');
            for (let j = 0; j < allItems.length; j++) {
                allItems[j].classList.remove('selected');
            }
            
            // Aggiungi la classe selected all'elemento corrente
            this.classList.add('selected');
            
            let value = this.getAttribute('data-value');
            let imageSrc = this.querySelector('img').src;
            
            // Aggiorna l'anteprima
            document.getElementById('preview-image').src = imageSrc;
            
            // Aggiorna il campo nascosto
            document.getElementById('immagine_default').value = value;
            
            // Resetta il campo di caricamento file
            document.getElementById('profile_image').value = '';
        });
    }
}

// Funzione per aggiungere validazione in tempo reale ai campi
function setupRealtimeValidation() {
    // Array di oggetti con i selettori e le loro funzioni di validazione
    let validationFields = [
        { id: 'username', validator: validaUsername },
        { id: 'email', validator: validaEmail },
        { id: 'first_name', validator: validaNomeCognome },
        { id: 'last_name', validator: validaNomeCognome },
        { id: 'phone', validator: validaTelefono },
        { id: 'birthdate', validator: validaDataNascita }
    ];
    
    // Aggiungi listener per ogni campo
    for (let i = 0; i < validationFields.length; i++) {
        let field = document.getElementById(validationFields[i].id);
        if (field) {
            field.addEventListener('blur', function() {
                let isValid = field.value && validationFields[i].validator(field.value);
                toggleValidationFeedback(field, isValid);
            });
        }
    }
    
    // Gestione speciale per i campi password
    let newPassword = document.getElementById('new_password');
    if (newPassword) {
        newPassword.addEventListener('blur', function() {
            if (this.value) {
                toggleValidationFeedback(this, validaPassword(this.value));
            }
        });
    }
    
    let confirmPassword = document.getElementById('confirm_password');
    if (confirmPassword) {
        confirmPassword.addEventListener('blur', function() {
            if (this.value) {
                let newPwdValue = document.getElementById('new_password').value;
                toggleValidationFeedback(this, this.value === newPwdValue);
            }
        });
    }
}

// Inizializzazione al caricamento della pagina
document.addEventListener('DOMContentLoaded', function() {
    // Setup gestione immagini predefinite
    setupDefaultImageSelection();
    
    // Setup validazione in tempo reale
    setupRealtimeValidation();
    
    // Setup anteprima immagine
    let profileImageUpload = document.getElementById('profile_image');
    if (profileImageUpload) {
        profileImageUpload.addEventListener('change', handleImagePreview);
    }
    
    // Setup validazione form al submit
    let form = document.getElementById('profileForm');
    if (form) {
        form.addEventListener('submit', validateForm);
    }
});