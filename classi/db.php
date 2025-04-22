<?php
    $conn = new mysqli("localhost", "root", "", "nerdverse");
    
    if ($conn->connect_error) {
        die("Connessione fallita: " . $conn->connect_error);
    }
?>
