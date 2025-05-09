<?php
    /*
    {
        "status": "OK"|"ERR",
        "msg":"", | "data":[]
    }
    */

    session_start();
    require_once("../classi/db.php");

    if (!isset($_SESSION["utente_id"])) {
        echo json_encode(["status" => "ERR", "msg" => "Non sei loggato."]);
        die();
    }

    if (!$conn) {
        echo json_encode(["status" => "ERR", "msg" => "Errore di connessione al database."]);
        die();
    }

    $utente_id = intval($_SESSION["utente_id"]);

    // Query per ottenere solo le attività fumetto più recenti per l'utente loggato
    $sql = "
        SELECT af.titolo, af.riferimento_api, af.status, af.punteggio, af.numero_letti, af.data_inizio, af.data_fine, 
            af.note, af.preferito, af.nome_volume, af.anno_uscita, af.data_ora, af.numero_fumetto, af.riletture
        FROM attivita_fumetto af
        INNER JOIN (
            SELECT riferimento_api, MAX(data_ora) as max_data
            FROM attivita_fumetto
            WHERE utente_id = ?
            GROUP BY riferimento_api
        ) latest ON af.riferimento_api = latest.riferimento_api 
                AND af.data_ora = latest.max_data
        WHERE af.utente_id = ?
        ORDER BY af.data_ora DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $utente_id, $utente_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result) {
        echo json_encode(["status" => "ERR", "msg" => "Errore nella query."]);
        die();
    }

    $attivita = [];
    $api_key = "22c2e6718a7614c00a5fd89e2a6d8a4cfe8274ce";

    while ($riga = $result->fetch_assoc()) {
        $id_api = (int)$riga["riferimento_api"];
        $immagine = "";

        // Recupero dell'immagine dal ComicVine API
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
                if (isset($data["results"]["image"]["small_url"])) {
                    $immagine = $data["results"]["image"]["small_url"];
                } elseif (isset($data["results"]["image"]["original_url"])) {
                    $immagine = $data["results"]["image"]["original_url"];
                }
            }
        }

        // Aggiungo i dati dell'attività fumetto all'array
        $attivita[] = [
            "titolo" => $riga["titolo"],
            "riferimento_api" => $riga["riferimento_api"],
            "status" => $riga["status"],
            "punteggio" => $riga["punteggio"],
            "numero_letti" => (int)$riga["numero_letti"],
            "data_inizio" => $riga["data_inizio"],
            "data_fine" => $riga["data_fine"],
            "note" => $riga["note"],
            "preferito" => (int)$riga["preferito"],
            "nome_volume" => $riga["nome_volume"],
            "anno_uscita" => $riga["anno_uscita"],
            "data_ora" => $riga["data_ora"],
            "numero_fumetto" => $riga["numero_fumetto"],
            "riletture" => (int)$riga["riletture"],
            "immagine" => $immagine
        ];
    }

    echo json_encode([
        "status" => "OK",
        "data" => $attivita
    ]);
    die();
?>
