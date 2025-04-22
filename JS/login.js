 // Funzione di validazione email (regex)
 function validaEmail(email) {
    return /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/.test(email);
}

// Funzione di validazione username (solo alfanumerico)
function validaUsername(username) {
    return /^[a-zA-Z0-9_]{3,20}$/.test(username);
}

// Funzione di validazione password (minimo 8 caratteri)
function validaPassword(password) {
    return password.length >= 8;
}

// Funzione per la validazione del form
function validaForm(event) {
    let errori = [];
    let username = document.getElementById("username").value;
    let password = document.getElementById("password").value;

    if (!validaUsername(username)) {
        errori.push("Username non valido. Deve essere alfanumerico e tra 3 e 20 caratteri.");
    }

    if (!validaPassword(password)) {
        errori.push("La password deve essere lunga almeno 8 caratteri.");
    }

    if (errori.length > 0) {
        alert(errori.join("\n"));
        event.preventDefault(); // Impedisce l'invio del form
    }
}