<?php
require_once('classi/db.php');
require_once('classi/Utente.php');
session_start();

// Verifica se l'utente è loggato
if (!isset($_SESSION['utente_id'])) {
    header("Location: login.php");
    exit();
}

$errore = "";
$messaggio = "";
$user_id = $_SESSION['utente_id'];

// Ottieni i dati attuali dell'utente
$stmt = $conn->prepare("SELECT * FROM utenti WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$utente = $result->fetch_assoc();

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
    return !empty($username) && strlen($username) >= 3;
}

function validaDataNascita($data) {
    $anno = (int)substr($data, 0, 4);
    return $anno >= 1900 && $anno <= date('Y');
}

// Modifica del profilo
if (isset($_POST['modifica_profilo'])) {
    $username = trim($_POST['username']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $birthdate = $_POST['birthdate'];
    $bio = trim($_POST['bio']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validazione dei campi
    $errori = [];
    
    if (empty($username) || empty($first_name) || empty($last_name) || empty($phone) || empty($email) || empty($birthdate)) {
        $errori[] = "Tutti i campi contrassegnati con * sono obbligatori";
    }
    
    if (!validaUsername($username)) {
        $errori[] = "Username non valido (minimo 3 caratteri)";
    }
    
    if (!validaNomeCognome($first_name)) {
        $errori[] = "Nome non valido";
    }
    
    if (!validaNomeCognome($last_name)) {
        $errori[] = "Cognome non valido";
    }
    
    if (!validaTelefono($phone)) {
        $errori[] = "Numero di telefono non valido";
    }
    
    if (!validaEmail($email)) {
        $errori[] = "Email non valida";
    }
    
    if (!validaDataNascita($birthdate)) {
        $errori[] = "Data di nascita non valida (deve essere successiva al 1900)";
    }
    
    if (empty($errori)) {
        // Verifica se lo username è già in uso (escludi l'utente corrente)
        $stmt_check = $conn->prepare("SELECT id FROM utenti WHERE username = ? AND id != ?");
        $stmt_check->bind_param("si", $username, $user_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if ($result_check->num_rows > 0) {
            $errore = "Username già in uso da un altro utente";
        } else {
            // Verifica se l'email è già in uso (escludi l'utente corrente)
            $stmt_check = $conn->prepare("SELECT id FROM utenti WHERE email = ? AND id != ?");
            $stmt_check->bind_param("si", $email, $user_id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            
            if ($result_check->num_rows > 0) {
                $errore = "Email già in uso da un altro utente";
            } else {
                // Gestione del cambio password
                $password_change = false;
                $password_hash = $utente['password']; // Mantieni la password esistente come default
                
                if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
                    // Verifica che la password attuale sia corretta
                    if (md5($current_password) != $utente['password']) {
                        $errore = "La password attuale non è corretta";
                    } else if (empty($new_password) || empty($confirm_password)) {
                        $errore = "Inserisci sia la nuova password che la conferma";
                    } else if ($new_password !== $confirm_password) {
                        $errore = "La nuova password e la conferma non corrispondono";
                    } else if (!validaPassword($new_password)) {
                        $errore = "La nuova password deve essere lunga almeno 8 caratteri";
                    } else {
                        $password_hash = md5($new_password);
                        $password_change = true;
                    }
                }
                
                // Gestione dell'upload dell'immagine
                $profile_image = $utente['profile_image']; // Mantieni l'immagine esistente come default
                
                // Gestione immagine predefinita
                if (isset($_POST['immagine_default']) && !empty($_POST['immagine_default'])) {
                    $profile_image = "default_profiles/" . $_POST['immagine_default'];
                }
                // Gestione upload nuova immagine
                else if (isset($_FILES['profile_image']) && $_FILES['profile_image']['size'] > 0) {
                    $target_dir = "uploads/";
                    if (!file_exists($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }
                    
                    $file_extension = strtolower(pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION));
                    $allowed_extensions = array("jpg", "jpeg", "png", "gif");
                    
                    if (!in_array($file_extension, $allowed_extensions)) {
                        $errore = "Sono permessi solo file JPG, JPEG, PNG e GIF";
                    } else if ($_FILES["profile_image"]["size"] > 2000000) { // 2MB max
                        $errore = "Il file è troppo grande (max 2MB)";
                    } else {
                        $new_filename = md5(time() . $username) . '.' . $file_extension;
                        $target_file = $target_dir . $new_filename;
                        
                        if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
                            // Se esiste un'immagine precedente e non è un'immagine di default, la eliminiamo
                            if ($profile_image && file_exists($profile_image) && strpos($profile_image, 'default_profiles/') === false) {
                                unlink($profile_image);
                            }
                            $profile_image = $target_file;
                        } else {
                            $errore = "Si è verificato un errore nel caricamento dell'immagine";
                        }
                    }
                }
                
                // Se non ci sono errori, aggiorna il profilo
                if (empty($errore)) {
                    // Utilizza il metodo updateProfile della classe Utente
                    $risultato = Utente::updateProfile($conn, $user_id, $username, $first_name, $last_name, 
                                                      $phone, $email, $birthdate, $profile_image, 
                                                      $password_hash, $bio);
                    
                    if (strpos($risultato, "successo") !== false) {
                        $messaggio = $risultato;
                        // Aggiorna i dati nella sessione se necessario
                        $_SESSION['username'] = $username;
                        if ($password_change) {
                            $messaggio .= " La password è stata modificata.";
                        }
                        
                        // Ricarica i dati aggiornati dell'utente
                        $stmt = $conn->prepare("SELECT * FROM utenti WHERE id = ?");
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $utente = $result->fetch_assoc();
                    } else {
                        $errore = $risultato;
                    }
                }
            }
        }
    } else {
        $errore = implode("<br>", $errori);
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifica Profilo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="CSS/pannello_modifica.css">
    <script src="JS/registrazione.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">Modifica Profilo</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errore)) { ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $errore; ?>
                            </div>
                        <?php } ?>
                        
                        <?php if (!empty($messaggio)) { ?>
                            <div class="alert alert-success" role="alert">
                                <?php echo $messaggio; ?>
                            </div>
                        <?php } ?>
                        
                        <form action="" method="post" enctype="multipart/form-data" id="profileForm" onsubmit="return validateForm(event)">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="username" class="form-label">Username * <small class="text-muted">(minimo 3 caratteri)</small></label>
                                    <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($utente['username']); ?>" required>
                                    <div class="invalid-feedback">Inserisci un username valido (minimo 3 caratteri)</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($utente['email']); ?>" required>
                                    <div class="invalid-feedback">Inserisci un'email valida</div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">Immagine profilo</label>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <div class="text-center">
                                            <img id="preview-image" src="<?php echo htmlspecialchars($utente['profile_image']); ?>" alt="Immagine profilo" class="img-thumbnail rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label for="profile_image" class="form-label">Carica nuova immagine</label>
                                            <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                                            <small class="form-text text-muted">Formati supportati: JPG, JPEG, PNG, GIF. Max 2MB.</small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Oppure scegli immagine predefinita:</label>
                                            <div class="profile-images-container">
                                                <?php
                                                    $directory = "default_profiles/";
                                                    // Verifica se la directory esiste
                                                    if (file_exists($directory)) {
                                                        $files = scandir($directory);
                                                        
                                                        for ($i = 0; $i < count($files); $i++) {
                                                            $file = $files[$i];
                                                            $path = $directory . $file;
                                                            
                                                            // Filtra solo immagini valide (no . e ..)
                                                            if ($file !== "." && $file !== ".." && preg_match('/\.(jpg|jpeg|png|gif)$/i', $file)) {
                                                                $nome = ucwords(str_replace('_', ' ', pathinfo($file, PATHINFO_FILENAME)));
                                                                $selected = ($utente['profile_image'] == $path) ? "selected" : "";
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
                                            <input type="hidden" name="immagine_default" id="immagine_default">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="first_name" class="form-label">Nome *</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($utente['first_name']); ?>" required>
                                    <div class="invalid-feedback">Inserisci un nome valido</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="last_name" class="form-label">Cognome *</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($utente['last_name']); ?>" required>
                                    <div class="invalid-feedback">Inserisci un cognome valido</div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Telefono *</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($utente['phone']); ?>" required>
                                    <div class="invalid-feedback">Inserisci un numero di telefono valido (8-15 cifre)</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="birthdate" class="form-label">Data di nascita * <small class="text-muted">(anno min: 1900)</small></label>
                                    <input type="date" class="form-control" id="birthdate" name="birthdate" value="<?php echo htmlspecialchars($utente['birthdate']); ?>" required>
                                    <div class="invalid-feedback">Inserisci una data di nascita valida (anno >= 1900)</div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="bio" class="form-label">Bio (opzionale)</label>
                                <textarea class="form-control" id="bio" name="bio" rows="4"><?php echo htmlspecialchars($utente['bio'] ?? ''); ?></textarea>
                                <small class="form-text text-muted">Raccontaci di te, delle tue passioni e dei tuoi interessi nerd!</small>
                            </div>
                            
                            <hr>
                            <h4 class="mb-3">Modifica Password</h4>
                            <p class="text-muted small">Compila questi campi solo se desideri cambiare la tua password</p>
                            
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Password attuale</label>
                                <input type="password" class="form-control" id="current_password" name="current_password">
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="new_password" class="form-label">Nuova password <small class="text-muted">(min. 8 caratteri)</small></label>
                                    <input type="password" class="form-control" id="new_password" name="new_password">
                                    <div class="invalid-feedback">La password deve essere lunga almeno 8 caratteri</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="confirm_password" class="form-label">Conferma nuova password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                    <div class="invalid-feedback">Le password non corrispondono</div>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                <a href="profilo.php" class="btn btn-secondary me-md-2">Annulla</a>
                                <button type="submit" name="modifica_profilo" class="btn btn-primary">Salva modifiche</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>