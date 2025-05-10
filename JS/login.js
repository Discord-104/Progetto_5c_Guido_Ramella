// Funzione di validazione username (solo alfanumerico)
function validaUsername(username) {
    let usernameRegex = /^[a-zA-Z0-9_]{3,20}$/;
    return usernameRegex.test(username);
}

// Funzione di validazione password (minimo 8 caratteri)
function validaPassword(password) {
    return password && password.length >= 8;
}

// Funzione per la validazione del form
function validaForm(event) {
    let isValid = true;
    
    let username = document.getElementById("username");
    let password = document.getElementById("password");
    
    // Reset dello stato di validazione
    let elementiForm = [username, password];
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
    
    // Validazione password
    if (!validaPassword(password.value)) {
        password.classList.add('is-invalid');
        isValid = false;
    } else {
        password.classList.add('is-valid');
    }
    
    // Impedisci l'invio del form se non è valido
    if (!isValid) {
        event.preventDefault();
    }
    
    return isValid;
}

// Configurazione degli eventi al caricamento del DOM
document.addEventListener('DOMContentLoaded', function() {
    // Aggiungi evento di validazione al form
    const form = document.getElementById('loginForm');
    if (form) {
        form.addEventListener('submit', validaForm);
    }
    
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
        });
    }
});

function mostraErrore(messaggio) {
    // Rimuovi eventuali box di errore esistenti
    let errorBox = document.querySelector('.error-box');
    if (errorBox) {
        errorBox.remove();
    }
            
    // Crea il nuovo box di errore
    errorBox = document.createElement('div');
    errorBox.className = 'error-box';
    errorBox.innerHTML = `
        <i class="fas fa-exclamation-circle"></i>
         <p>${messaggio}</p>
    `;
            
    // Inserisci il box di errore prima del form
    const form = document.getElementById('loginForm');
    form.parentNode.insertBefore(errorBox, form);
}