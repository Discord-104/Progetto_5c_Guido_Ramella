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

function validaForm(event) {
    let errori = [];
    let nome = document.getElementById("first_name").value;
    let cognome = document.getElementById("last_name").value;
    let telefono = document.getElementById("phone").value;
    let email = document.getElementById("email").value;
    let password = document.getElementById("password").value;
    let conferma = document.getElementById("confermaPassword").value;

    if (!validaNomeCognome(nome)) {
        errori.push("Nome non valido.");
    }

    if (!validaNomeCognome(cognome)) {
        errori.push("Cognome non valido.");
    }

    if (!validaTelefono(telefono)) {
        errori.push("Telefono non valido.");
    }

    if (!validaEmail(email)) {
        errori.push("Email non valida.");
    }

    if (!validaPassword(password)) {
        errori.push("Password troppo corta.");
    }

    if (password !== conferma) {
        errori.push("Le password non coincidono.");
    }

    if (errori.length > 0) {
        alert(errori.join("\n"));
        event.preventDefault();  // Impedisce l'invio del form
    }
}
