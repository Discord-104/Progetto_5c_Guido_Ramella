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

    $utente_id = intval($_SESSION["utente_id"]);

    // Query per ottenere le attività videogioco più recenti per l'utente
    $sql = "
        SELECT av.guid, av.titolo, av.data_uscita, av.status, av.punteggio, av.ore_giocate, 
            av.start_date, av.end_date, av.note, av.rigiocato, av.preferito, av.data_ora
        FROM attivita_videogioco av
        INNER JOIN (
            SELECT guid, MAX(data_ora) as max_data
            FROM attivita_videogioco
            WHERE utente_id = ?
            GROUP BY guid
        ) latest ON av.guid = latest.guid AND av.data_ora = latest.max_data
        WHERE av.utente_id = ?
        ORDER BY av.data_ora DESC
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(["status" => "ERR", "msg" => "Errore nella preparazione della query."]);
        die();
    }
    $stmt->bind_param("ii", $utente_id, $utente_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result) {
        echo json_encode(["status" => "ERR", "msg" => "Errore nell'esecuzione della query."]);
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
            "guid" => $riga["guid"],
            "titolo" => $riga["titolo"],
            "data_uscita" => $riga["data_uscita"],
            "status" => $riga["status"],
            "punteggio" => (float)$riga["punteggio"],
            "ore_giocate" => (int)$riga["ore_giocate"],
            "start_date" => $riga["start_date"],
            "end_date" => $riga["end_date"],
            "note" => $riga["note"],
            "rigiocato" => (int)$riga["rigiocato"],
            "preferito" => (int)$riga["preferito"],
            "data_ora" => $riga["data_ora"],
            "immagine" => $immagine
        ];
    }

    echo json_encode(["status" => "OK", "data" => $attivita]);
    die();
?>
