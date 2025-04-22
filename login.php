<?php
    session_start();
    require_once("classi/db.php");
    require_once("classi/Utente.php");

    // Funzione per validare lo username con una regex
    function validaUsername($username) {
        return preg_match("/^[a-zA-Z0-9_]{3,20}$/", $username);
    }

    // Funzione per validare la password (almeno 8 caratteri)
    function validaPassword($password) {
        return strlen($password) >= 8;
    }

    // Login lato server
    if (isset($_POST["username"]) && isset($_POST["password"])) {
        $username = $_POST["username"];
        $password = $_POST["password"];

        // Controllo lato server con regex
        if (!validaUsername($username)) {
            echo "Username non valido. Deve essere alfanumerico e tra 3 e 20 caratteri.<br>";
        } elseif (!validaPassword($password)) {
            echo "La password deve essere lunga almeno 8 caratteri.<br>";
        } else {
            // Verifica delle credenziali nel database
            $utente = Utente::login($conn, $username, $password);

            if ($utente) {
                $_SESSION["username"] = $utente->getUsername();
                $_SESSION["utente_id"] = $utente->getId();
                header("Location: home.php");
            } else {
                echo "Credenziali errate!<br>";
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="JS/login.js"></script>
</head>
<body>
    <!-- Form di login -->
    <form method="post" onsubmit="validaForm(event)">
        <input type="text" name="username" id="username" placeholder="Username" required><br>
        <input type="password" name="password" id="password" placeholder="Password" required><br>
        <button type="submit">Login</button>
        <p>Non hai un account? <a href="registrazione.php">Registrati qui</a></p>
    </form>
</body>
</html>
