<?php
    session_start();
    session_unset();
    session_destroy();
    header("Location: login.php"); // Dopo il logout, reindirizza alla pagina di login
    exit();
?>
