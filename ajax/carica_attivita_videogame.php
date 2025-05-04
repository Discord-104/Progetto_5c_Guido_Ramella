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

    // Query per ottenere le attività videogioco
    $sql = "SELECT u.username, av.guid, av.titolo, av.data_uscita, av.status, av.ore_giocate
            FROM attivita_videogioco av
            INNER JOIN utenti u ON av.utente_id = u.id
            ORDER BY av.data_ora DESC";

    $result = $conn->query($sql);

    if (!$result) {
        echo json_encode(["status" => "ERR", "msg" => "Errore nella query."]);
        die();
    }

    $attivita = [];
    $api_key = "bb709d6b2114c61e3f0c9999834b43918d1a2427";

    while ($riga = $result->fetch_assoc()) {
        $guid = $riga["guid"];
        $immagine = "";

        if (!empty($guid)) {
            $url = "https://www.giantbomb.com/api/game/" . $guid . "/?api_key=" . $api_key . "&format=json";
            $opts = [
                "http" => [
                    "header" => "User-Agent: GiantBomb PHP Client\r\n"
                ]
            ];
            $context = stream_context_create($opts);
            $response = file_get_contents($url, false, $context);

            if ($response !== false) {
                $data = json_decode($response, true);
                if (isset($data["results"]["image"]["small_url"])) {
                    $immagine = $data["results"]["image"]["small_url"];
                } elseif (isset($data["results"]["image"]["original_url"])) {
                    $immagine = $data["results"]["image"]["original_url"]; 
                }
            }
        }

        $attivita[] = [
            "tipo" => "videogioco",
            "username" => $riga["username"],
            "titolo" => $riga["titolo"],
            "status" => $riga["status"],
            "data_uscita" => $riga["data_uscita"],
            "ore_giocate" => $riga["ore_giocate"],
            "immagine" => $immagine
        ];
    }

    echo json_encode(["status" => "OK", "data" => $attivita]);
    die();
?>
