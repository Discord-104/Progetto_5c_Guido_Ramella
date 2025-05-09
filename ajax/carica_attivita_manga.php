<?php
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

    // Query per ottenere solo le attività manga più recenti per l'utente loggato
    $sql = "
        SELECT am.titolo, am.riferimento_api, am.status, am.punteggio, am.capitoli_letti,
            am.volumi_letti, am.data_inizio, am.data_fine, am.note, am.rereading, am.preferito,
            am.anno, am.formato
        FROM attivita_manga am
        INNER JOIN (
            SELECT riferimento_api, MAX(data_ora) as max_data
            FROM attivita_manga
            WHERE utente_id = ?
            GROUP BY riferimento_api
        ) latest ON am.riferimento_api = latest.riferimento_api 
                AND am.data_ora = latest.max_data
        WHERE am.utente_id = ?
        ORDER BY am.data_ora DESC
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

    while ($riga = $result->fetch_assoc()) {
        $id_api = (int)$riga["riferimento_api"];
        $immagine = "";

        if ($id_api > 0) {
            $query = [
                'query' => '
                    query ($id: Int) {
                        Media(id: $id, type: MANGA) {
                            coverImage {
                                large
                            }
                        }
                    }
                ',
                'variables' => ['id' => $id_api]
            ];

            $opts = [
                'http' => [
                    'method'  => 'POST',
                    'header'  => "Content-Type: application/json\r\nAccept: application/json\r\n",
                    'content' => json_encode($query)
                ]
            ];

            $context = stream_context_create($opts);
            $response = file_get_contents('https://graphql.anilist.co', false, $context);

            if ($response !== false) {
                $data = json_decode($response, true);
                if (isset($data['data']['Media']['coverImage']['large'])) {
                    $immagine = $data['data']['Media']['coverImage']['large'];
                }
            }
        }

        $attivita[] = [
            "titolo" => $riga["titolo"],
            "riferimento_api" => $riga["riferimento_api"],
            "status" => $riga["status"],
            "punteggio" => $riga["punteggio"],
            "capitoli_letti" => (int)$riga["capitoli_letti"],
            "volumi_letti" => (int)$riga["volumi_letti"],
            "data_inizio" => $riga["data_inizio"],
            "data_fine" => $riga["data_fine"],
            "note" => $riga["note"],
            "rereading" => (int)$riga["rereading"],
            "preferito" => (int)$riga["preferito"],
            "anno" => $riga["anno"],
            "formato" => $riga["formato"],
            "immagine" => $immagine
        ];
    }

    echo json_encode([
        "status" => "OK",
        "data" => $attivita
    ]);
    die();
?>
