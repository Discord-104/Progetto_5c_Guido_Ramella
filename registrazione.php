<?php
require_once("classi/db.php");
require_once("classi/Utente.php");

session_start();

$error = "";
$success = "";

// Validazione lato server con regex
function validaEmail($email) {
    return preg_match('/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/', $email);
}

function validaNomeCognome($str) {
    return preg_match('/^[a-zA-ZàèéìòùÀÈÉÌÒÙ\s]+$/', $str);
}

function validaTelefono($phone) {
    return preg_match('/^[0-9]{8,15}$/', $phone);
}

function validaPassword($password) {
    return strlen($password) >= 8;
}

// Se tutti i campi richiesti sono settati (quindi c'è un invio)
if (isset($_POST['username'], $_POST['first_name'], $_POST['last_name'], $_POST['phone'], $_POST['email'], $_POST['birthdate'], $_POST['password'], $_POST['confermaPassword'])) {
    
    // Estrai i dati dal form
    $username = $_POST['username'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $birthdate = $_POST['birthdate'];
    $password = $_POST['password'];
    $confermaPassword = $_POST['confermaPassword'];
    $tipo = 'utente';

    // Gestione immagine profilo
    $profile_image = '';
    if (!empty($_FILES['immagine']['name'])) {
        $uploadDir = "uploads/";
        $profile_image = $uploadDir . basename($_FILES["immagine"]["name"]);
        move_uploaded_file($_FILES["immagine"]["tmp_name"], $profile_image);
    } else if (isset($_POST["immagine_default"])) {
        $profile_image = "default_profiles/" . $_POST["immagine_default"];
    }

    // Validazioni
    $errori = [];

    if (!validaNomeCognome($first_name)) {
        $errori[] = "Nome non valido.";
    }
 
    if (!validaNomeCognome($last_name)) {
        $errori[] = "Cognome non valido.";
    }
 
    if (!validaTelefono($phone)) {
        $errori[] = "Numero di telefono non valido.";
    }
 
    if (!validaEmail($email)) {
        $errori[] = "Email non valida.";
    }
 
    if (!validaPassword($password)) {
        $errori[] = "Password troppo corta.";
    }
 
    if ($password !== $confermaPassword) {
        $errori[] = "Le password non coincidono.";
    }

    if (empty($errori)) {
        $res = Utente::register($conn, $username, $first_name, $last_name, $phone, $email, $birthdate, $profile_image, $password, $tipo);
        if (strpos($res, 'successo') !== false) {
            $success = $res;
        } else {
            $error = $res;
        }
    } else {
        $error = implode("<br>", $errori);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registrazione</title>
    <script src="JS/registrazione.js"></script>
    <script src="JS/image.js"></script>
    <link rel="stylesheet" href="CSS/image.css">
</head>
<body>
    <h2>Registrazione</h2>

    <?php 
        if ($error){
            echo "<p style='color:red;'>$error</p>"; 
        }
        if ($success){
            echo "<p style='color:green;'>$success</p>";
        }
    ?>

    <form method="post" enctype="multipart/form-data" onsubmit="validaForm(event)">
        <label>Username:</label>
        <input type="text" name="username" required><br>

        <label>Nome:</label>
        <input type="text" name="first_name" id="first_name" required><br>

        <label>Cognome:</label>
        <input type="text" name="last_name" id="last_name" required><br>

        <label>Telefono:</label>
        <input type="text" name="phone" id="phone" required><br>

        <label>Email:</label>
        <input type="email" name="email" id="email" required><br>

        <label>Data di nascita:</label>
        <input type="date" name="birthdate" required><br>

        <label>Password:</label>
        <input type="password" name="password" id="password" required><br>

        <label>Conferma password:</label>
        <input type="password" name="confermaPassword" id="confermaPassword" required><br>

        <label>Carica immagine profilo:</label>
        <input type="file" name="immagine" accept="image/*"><br>

        <label>Oppure scegli immagine predefinita:</label>
        <div class="dropdown">
            <button class="dropbtn">Seleziona immagine</button>
            <div class="dropdown-content">
                <div class="dropdown-item" data-value="avatar.png">
                    <img src="default_profiles/avatar.png" alt="Avatar 1" class="avatar-image">
                    Avatar 1
                </div>
                <div class="dropdown-item" data-value="avatar2.png">
                    <img src="default_profiles/avatar2.png" alt="Avatar 2" class="avatar-image">
                    Avatar 2
                </div>
                <div class="dropdown-item" data-value="avatar3.png">
                    <img src="default_profiles/avatar3.png" alt="Avatar 3" class="avatar-image">
                    Avatar 3
                </div>
            </div>
            <input type="hidden" name="immagine_default" id="immagine_default">
        </div>

        <input type="submit" value="Registrati">
    </form>
</body>
</html>
