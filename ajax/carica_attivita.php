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

    $sql_anime = "
        SELECT aa.titolo, aa.riferimento_api, aa.status, aa.punteggio, aa.episodi_visti,
            aa.data_inizio, aa.data_fine, aa.note, aa.rewatch, aa.preferito, aa.anno_uscita, aa.formato
        FROM attivita_anime aa
        INNER JOIN (
            SELECT riferimento_api, MAX(data_ora) as max_data
            FROM attivita_anime
            WHERE utente_id = ?
            GROUP BY riferimento_api
        ) latest ON aa.riferimento_api = latest.riferimento_api 
                AND aa.data_ora = latest.max_data
        WHERE aa.utente_id = ?
        ORDER BY aa.data_ora DESC
    ";

    $stmt = $conn->prepare($sql_anime);
    $stmt->bind_param("ii", $utente_id, $utente_id);
    $stmt->execute();
    $result_anime = $stmt->get_result();

    $attivita = [];

    if ($result_anime) {
        while ($riga = $result_anime->fetch_assoc()) {
            $id_api = (int)$riga["riferimento_api"];
            $immagine = "";

            if ($id_api > 0) {
                $query = [
                    'query' => '
                        query ($id: Int) {
                            Media(id: $id, type: ANIME) {
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
                        'method' => 'POST',
                        'header' => "Content-Type: application/json\r\nAccept: application/json\r\n",
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
                "episodi_visti" => (int)$riga["episodi_visti"],
                "data_inizio" => $riga["data_inizio"],
                "data_fine" => $riga["data_fine"],
                "note" => $riga["note"],
                "rewatch" => (int)$riga["rewatch"],
                "preferito" => (int)$riga["preferito"],
                "anno_uscita" => $riga["anno_uscita"],
                "formato" => $riga["formato"],
                "immagine" => $immagine
            ];
        }
    }

    echo json_encode([
        "status" => "OK",
        "data" => $attivita
    ]);
    die();
?>
