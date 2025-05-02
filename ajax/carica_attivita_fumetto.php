<?php
/*
{
    "status": "OK"|"ERR",
    "msg":"", | "data":[]
}
*/

session_start();
require_once("../classi/db.php");

// Controllo se l'utente è autenticato
if (!isset($_SESSION["utente_id"])) {
    echo json_encode(["status" => "ERR", "msg" => "Non sei loggato."]);
    die();
}

// Controllo se c'è il DB
if (!$conn) {
    echo json_encode(["status" => "ERR", "msg" => "Errore di connessione al database."]);
    die();
}

// Query per ottenere le attività fumetto
$sql = "SELECT u.username, af.titolo, af.riferimento_api, af.numero_letti, af.status, af.nome_volume, af.numero_volume
        FROM attivita_fumetto af
        INNER JOIN utenti u ON af.utente_id = u.id
        ORDER BY af.data_ora DESC";

$result = $conn->query($sql);

if (!$result) {
    echo json_encode(["status" => "ERR", "msg" => "Errore nella query."]);
    die();
}

$attivita = [];
$api_key = "22c2e6718a7614c00a5fd89e2a6d8a4cfe8274ce";

while ($riga = $result->fetch_assoc()) {
    $id_api = (int)$riga["riferimento_api"];
    $immagine = "";

    if ($id_api > 0) {
        $url = "https://comicvine.gamespot.com/api/issue/4000-" . $id_api . "/?api_key=" . $api_key . "&format=json";
        $opts = [
            "http" => [
                "header" => "User-Agent: ComicVine PHP Client\r\n"
            ]
        ];
        $context = stream_context_create($opts);
        $response = file_get_contents($url, false, $context);

        if ($response !== false) {
            $data = json_decode($response, true);
            if (isset($data["results"]["image"]["original_url"])) {
                $immagine = $data["results"]["image"]["original_url"];
            }
        }
    }

    $attivita[] = [
        "tipo" => "fumetto",
        "username" => $riga["username"],
        "titolo" => $riga["titolo"],
        "pagine_lette" => $riga["numero_letti"],
        "status" => $riga["status"],
        "nome_volume" => $riga["nome_volume"],
        "numero_volume" => $riga["numero_volume"],
        "immagine" => $immagine
    ];
}

echo json_encode(["status" => "OK", "data" => $attivita]);
die();
?>
