<?php
    require_once("../classi/db.php");
    session_start();

    $ret = [];

    // Controllo parametri in GET
    if (isset($_GET["utente_id"]) && isset($_GET["anime_id"])) {

        $utente_id = intval($_GET["utente_id"]);
        $anime_id = intval($_GET["anime_id"]);

        $titolo = "";
        if (isset($_GET["titolo"])) {
            $titolo = $_GET["titolo"];
        }

        $status = "Planning";
        if (isset($_GET["status"])) {
            $status = $_GET["status"];
        }

        $punteggio = null;
        if (isset($_GET["punteggio"])) {
            $punteggio = floatval($_GET["punteggio"]);
        }

        $episodi_visti = 0;
        if (isset($_GET["episodi_visti"])) {
            $episodi_visti = intval($_GET["episodi_visti"]);
        }

        $start_date = date("Y-m-d");
        if (isset($_GET["start_date"])) {
            $start_date = $_GET["start_date"];
        }

        $end_date = null;
        if (isset($_GET["end_date"])) {
            $end_date = $_GET["end_date"];
        }

        $note = null;
        if (isset($_GET["note"])) {
            $note = $_GET["note"];
        }

        $rewatch = 0;
        if (isset($_GET["rewatch"])) {
            $rewatch = intval($_GET["rewatch"]);
        }

        $preferito = 0;
        if (isset($_GET["preferito"])) {
            $preferito = intval($_GET["preferito"]);
        }

        // Controlli episodi
        if ($episodi_visti < 0) {
            $episodi_visti = 0;
        }

        // Controllo se esiste già attività
        $stmt = $conn->prepare("SELECT id FROM attivita_anime WHERE utente_id = ? AND riferimento_api = ?");
        $stmt->bind_param("ii", $utente_id, $anime_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            // Esiste già -> UPDATE
            $attivita_id = $row["id"];

            $stmt_update = $conn->prepare("UPDATE attivita_anime SET titolo=?, status=?, punteggio=?, episodi_visti=?, data_inizio=?, data_fine=?, note=?, rewatch=?, preferito=?, data_ora=NOW() WHERE id=?");
            $stmt_update->bind_param("ssdisssiii", $titolo, $status, $punteggio, $episodi_visti, $start_date, $end_date, $note, $rewatch, $preferito, $attivita_id);
            $stmt_update->execute();

        } else {
            // Non esiste -> INSERT
            $stmt_insert = $conn->prepare("INSERT INTO attivita_anime (utente_id, titolo, riferimento_api, status, punteggio, episodi_visti, data_inizio, data_fine, note, rewatch, preferito) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt_insert->bind_param("isisdisssii", $utente_id, $titolo, $anime_id, $status, $punteggio, $episodi_visti, $start_date, $end_date, $note, $rewatch, $preferito);
            $stmt_insert->execute();
        }

        $ret["status"] = "OK";

    } else {
        $ret["status"] = "ERROR";
        $ret["message"] = "Parametri GET mancanti.";
    }

    echo json_encode($ret);
    die();
?>
