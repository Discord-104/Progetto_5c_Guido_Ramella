<?php
    /* {
    "status": "OK"|"ERR",
    "msg":"", | "data":[]
    } */
    session_start();
    require_once("../classi/db.php");

    $ret = [];

    if (!isset($_SESSION["utente_id"])) {
        $ret["status"] = "ERR";
        $ret["msg"] = "Utente non autenticato.";
        echo json_encode($ret);
        die();
    }

    $utente_id = $_SESSION["utente_id"];

    $sql = "SELECT tipo, titolo, progresso, data_ora FROM attivita WHERE utente_id = ? ORDER BY data_ora DESC";
    $stmt = $conn->prepare($sql);

    if (!$stmt->execute([$utente_id])) {
        $ret["status"] = "ERR";
        $ret["msg"] = "Errore nella query.";
        echo json_encode($ret);
        die();
    }

    $ret["status"] = "OK";
    $ret["data"] = [];

    $righeTrovate = false;

    while ($row = $stmt->fetch()) {
        $righeTrovate = true;
        $riga = [];
        $riga["tipo"] = $row[0];
        $riga["titolo"] = $row[1];
        $riga["progresso"] = $row[2];
        $riga["data_ora"] = $row[3];
        $ret["data"][] = $riga;
    }

    if (!$righeTrovate) {
        $ret["msg"] = "Nessuna attivita trovata.";
    }

    echo json_encode($ret);
    die();
?>
