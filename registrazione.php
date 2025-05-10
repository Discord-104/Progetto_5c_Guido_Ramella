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

function validaUsername($username) {
    return strlen($username) >= 3;
}

function validaDataNascita($data) {
    $anno = intval(substr($data, 0, 4));
    return $anno >= 1900 && $anno <= date("Y");
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
    $bio = null;
if (isset($_POST['bio'])) {
    $bio = $_POST['bio'];
}
    $tipo = 'utente';

    // Inizializzazione array errori e gestione immagine profilo
    $errori = [];
    $erroriImmagine = [];
    $profile_image = '';
    $uploadDir = "uploads/";
    $maxSize = 2 * 1024 * 1024; // 2MB

    // Gestione immagine profilo
    if (!empty($_FILES['profile_image']['name'])) {
        // Verifica se il file è un'immagine
        $fileType = $_FILES["profile_image"]["type"];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        
        if (!in_array($fileType, $allowedTypes)) {
            $erroriImmagine[] = "Solo file JPG, PNG e GIF sono consentiti.";
        }
        
        // Verifica dimensione file
        if ($_FILES["profile_image"]["size"] > $maxSize) {
            $erroriImmagine[] = "L'immagine è troppo grande. Dimensione massima consentita: 2MB.";
        }
        
        // Se non ci sono errori, procedi con l'upload
        if (empty($erroriImmagine)) {
            // Crea una directory di upload se non esiste
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Genera un nome file univoco con timestamp
            $timestamp = date('Ymd_His') . '_' . uniqid();
            $fileExt = pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION);
            
            // Usa il nome file fornito dal client, se disponibile
            if (isset($_POST['new_file_name']) && !empty($_POST['new_file_name'])) {
                $fileName = $_POST['new_file_name'];
            } else {
                $fileName = "user_{$timestamp}.{$fileExt}";
            }
            
            $profile_image = $uploadDir . $fileName;
            
            // Sposta il file
            if (!move_uploaded_file($_FILES["profile_image"]["tmp_name"], $profile_image)) {
                $erroriImmagine[] = "Errore durante il caricamento dell'immagine. Riprova.";
                $profile_image = '';
            }
        }
    } else if (isset($_POST["immagine_default"]) && !empty($_POST["immagine_default"])) {
        // Usa un'immagine predefinita
        // Verifica che il file esista per evitare path traversal attacks
        $defaultFile = $_POST["immagine_default"];
        
        // Rimuove eventuali caratteri pericolosi
        $defaultFile = basename($defaultFile);
        
        if (file_exists("default_profiles/" . $defaultFile)) {
            $profile_image = "default_profiles/" . $defaultFile;
        } else {
            $erroriImmagine[] = "Immagine predefinita non trovata.";
        }
    } else {
        // Se nessuna immagine è specificata, usa quella predefinita
        if (file_exists("images/default_avatar.png")) {
            $profile_image = "images/default_avatar.png";
        } else {
            $erroriImmagine[] = "Immagine predefinita non trovata.";
        }
    }

    // Aggiunta degli errori dell'immagine all'array degli errori
    if (!empty($erroriImmagine)) {
        $errori = array_merge($errori, $erroriImmagine);
    }

    // Validazioni
    if (!validaUsername($username)) {
        $errori[] = "Username non valido: deve contenere almeno 3 caratteri.";
    }

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
    
    if (!validaDataNascita($birthdate)) {
        $errori[] = "Data di nascita non valida.";
    }
 
    if (!validaPassword($password)) {
        $errori[] = "Password troppo corta: deve contenere almeno 8 caratteri.";
    }
 
    if ($password !== $confermaPassword) {
        $errori[] = "Le password non coincidono.";
    }

    if (empty($errori)) {
        $res = Utente::register($conn, $username, $first_name, $last_name, $phone, $email, $birthdate, $profile_image, $password, $tipo, $bio);
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
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrazione - NerdVerse</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="CSS/registrazione.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="JS/registrazione.js"></script>
    <script src="JS/image.js"></script>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h2><i class="fas fa-user-plus"></i> Registrazione</h2>
                    </div>
                    <div class="card-body">
                        <?php 
                            if ($error){
                                echo "<div class='alert alert-danger'><i class='fas fa-exclamation-circle'></i> $error</div>"; 
                            }
                            if ($success){
                                echo "<div class='alert alert-success'><i class='fas fa-check-circle'></i> $success</div>";
                            }
                        ?>

                        <form method="post" enctype="multipart/form-data" id="registrationForm" onsubmit="return validaForm(event)">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="username" class="form-label">Username:</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" class="form-control" id="username" name="username" required>
                                    </div>
                                    <div class="invalid-feedback">Username non valido (minimo 3 caratteri)</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email:</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                    <div class="invalid-feedback">Email non valida</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="first_name" class="form-label">Nome:</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                                    </div>
                                    <div class="invalid-feedback">Nome non valido</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="last_name" class="form-label">Cognome:</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                                    </div>
                                    <div class="invalid-feedback">Cognome non valido</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Telefono:</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                        <input type="text" class="form-control" id="phone" name="phone" required>
                                    </div>
                                    <div class="invalid-feedback">Numero di telefono non valido (8-15 cifre)</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="birthdate" class="form-label">Data di nascita:</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                        <input type="date" class="form-control" id="birthdate" name="birthdate" required>
                                    </div>
                                    <div class="invalid-feedback">Data di nascita non valida</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="password" class="form-label">Password:</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                    </div>
                                    <div class="invalid-feedback">La password deve contenere almeno 8 caratteri</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="confermaPassword" class="form-label">Conferma password:</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" id="confermaPassword" name="confermaPassword" required>
                                    </div>
                                    <div class="invalid-feedback">Le password non coincidono</div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="bio" class="form-label">Bio (opzionale):</label>
                                <textarea class="form-control" id="bio" name="bio" rows="3" placeholder="Racconta qualcosa di te..."></textarea>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Carica immagine profilo:</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-image"></i></span>
                                        <input type="file" class="form-control" name="profile_image" id="profile_image" accept="image/*">
                                    </div>
                                    <small class="text-muted">Max 2MB - Formati supportati: JPG, PNG, GIF</small>
                                    <input type="hidden" name="new_file_name" id="new_file_name">
                                    
                                    <!-- Anteprima immagine -->
                                    <div class="text-center mt-3">
                                        <img id="preview-image" src="images/default_avatar.png" alt="Anteprima immagine profilo" class="img-thumbnail rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Oppure scegli immagine predefinita:</label>
                                    <div class="profile-images-container">
                                        <?php
                                            $directory = "default_profiles/";
                                            if (file_exists($directory)) {
                                                $files = scandir($directory);
                                                
                                                for ($i = 0; $i < count($files); $i++) {
                                                    $file = $files[$i];
                                                    $path = $directory . $file;
                                                    
                                                    // Filtra solo immagini valide (no . e ..)
                                                    if ($file !== "." && $file !== ".." && preg_match('/\.(jpg|jpeg|png|gif)$/i', $file)) {
                                                        $nome = ucwords(str_replace('_', ' ', pathinfo($file, PATHINFO_FILENAME)));
                                                        $selected = "";
                                                        if ($file === "default_avatar.png") {
                                                            $selected = "selected";
                                                        }
                                                        echo "<div class='profile-image-item $selected' data-value='$file'>
                                                                <img src='$path' alt='$nome' class='avatar-image'>
                                                                <div class='name'>$nome</div>
                                                              </div>";
                                                    }
                                                }
                                            } else {
                                                echo "<div>Nessuna immagine disponibile</div>";
                                            }
                                        ?>
                                    </div>
                                    <input type="hidden" name="immagine_default" id="immagine_default" value="default_avatar.png">
                                </div>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-user-plus"></i> Registrati</button>
                            </div>
                            
                            <div class="mt-3 text-center">
                                <p>Hai già un account? <a href="login.php" class="text-primary">Accedi qui</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>