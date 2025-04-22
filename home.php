<?php
    session_start();
    require_once("classi/db.php");
    require_once("classi/Utente.php");

    // Verifica se l'utente è loggato
    if (!isset($_SESSION["utente_id"])) {
        header("Location: login.php"); // Se non è loggato, reindirizza alla pagina di login
        exit();
    }

    // Ottieni i dati dell'utente loggato
    $utente_id = $_SESSION["utente_id"];
    $stmt = $conn->prepare("SELECT * FROM utenti WHERE id = ?");
    $stmt->bind_param("i", $utente_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $utente = $result->fetch_assoc();
    } else {
        echo "Utente non trovato!";
        exit();
    }
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/profilo.css"> 
    <title>Home</title>
</head>
<body>
    <h1>Benvenuto, <?php echo $utente['username']; ?>!</h1>
    
    <p><strong>Nome:</strong> <?php echo $utente['first_name'] . " " . $utente['last_name']; ?></p>
    <p><strong>Email:</strong> <?php echo $utente['email']; ?></p>
    
    <!-- Mostra l'immagine del profilo -->
    <p><strong>Immagine del profilo:</strong></p>
    <img src="<?php echo $utente['profile_image']; ?>" alt="Immagine profilo" width="150" height="150" class="profile-image">

    <br>
    <a href="logout.php">Esci</a>
</body>
</html>
