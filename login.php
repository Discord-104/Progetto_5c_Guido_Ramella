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

    $errore = ""; // Variabile per salvare il messaggio di errore

    // Login lato server
    if (isset($_POST["username"]) && isset($_POST["password"])) {
        $username = $_POST["username"];
        $password = $_POST["password"];

        // Controllo lato server con regex
        if (!validaUsername($username)) {
            $errore = "Username non valido. Deve essere alfanumerico e tra 3 e 20 caratteri.";
        } elseif (!validaPassword($password)) {
            $errore = "La password deve essere lunga almeno 8 caratteri.";
        } else {
            // Verifica delle credenziali nel database
            $utente = Utente::login($conn, $username, $password);

            if ($utente) {
                $_SESSION["username"] = $utente->getUsername();
                $_SESSION["utente_id"] = $utente->getId();
                header("Location: home.php");
            } else {
                $errore = "Credenziali errate! Verifica username e password.";
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="CSS/login.css">
    <script src="JS/login.js"></script>
</head>
<body>
    <div class="login-container">
        <div class="card animated fade-in">
            <h2 class="card-title">
                <span class="brand">Login</span>
            </h2>
            
            <!-- Mostra il box di errore se esiste un messaggio di errore -->
            <?php if (!empty($errore)): ?>
            <div class="error-box">
                <i class="fas fa-exclamation-circle"></i>
                <p><?php echo $errore; ?></p>
            </div>
            <?php endif; ?>
            
            <!-- Form di login -->
            <form method="post" id="loginForm" onsubmit="return validaForm(event)">
                <div class="mb-3">
                    <div class="input-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" class="form-control" name="username" id="username" placeholder="Username" required>
                    </div>
                    <div class="valid-feedback">
                        Username valido!
                    </div>
                    <div class="invalid-feedback">
                        Username non valido. Deve essere alfanumerico e tra 3 e 20 caratteri.
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
                    </div>
                    <div class="valid-feedback">
                        Password valida!
                    </div>
                    <div class="invalid-feedback">
                        La password deve essere lunga almeno 8 caratteri.
                    </div>
                </div>
                
                <div class="mb-3 form-check d-flex justify-content-between">
                    <div>
                        <input type="checkbox" class="form-check-input" id="ricordami" name="ricordami">
                        <label class="form-check-label" for="ricordami">Ricordami</label>
                    </div>
                    <div>
                        <a href="recupera-password.php" class="text-decoration-none">Password dimenticata?</a>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt me-2"></i>Accedi
                </button>
                
                <div class="separator">oppure accedi con</div>
                
                <div class="social-login">
                    <a href="#" class="social-btn fb">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="social-btn google">
                        <i class="fab fa-google"></i>
                    </a>
                    <a href="#" class="social-btn twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                </div>
                
                <div class="register-link">
                    <p>Non hai un account? <a href="registrazione.php">Registrati qui</a></p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>