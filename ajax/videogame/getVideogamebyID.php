<?php
session_start();

// Authentication check
if (!isset($_SESSION["utente_id"])) {
    $ret = ["status" => "ERR", "msg" => "Utente non autenticato."];
    echo json_encode($ret);
    die();
}

// Validate parameters
if (!isset($_GET["guid"])) {
    $ret = ["status" => "ERR", "msg" => "Parametro guid mancante."];
    echo json_encode($ret);
    die();
}

require_once ("api_helpers.php");
$guid = $_GET["guid"];

// Get basic game data first
$gameData = getBasicGameData($guid);

if ($gameData === false) {
    $ret = ["status" => "ERR", "msg" => "Errore nella richiesta all'API GiantBomb o nessun risultato trovato."];
    echo json_encode($ret);
    die();
}

// Return success with main game data
echo json_encode($gameData);
die();
?>
