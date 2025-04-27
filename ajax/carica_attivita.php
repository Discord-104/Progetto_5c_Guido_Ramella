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
        $ret = [];
        $ret["status"] = "ERR";
        $ret["msg"] = "Non sei loggato.";
        echo json_encode($ret);
        die();
    }

    // Controllo se c'è il DB
    if (!$conn) {
        $ret = [];
        $ret["status"] = "ERR";
        $ret["msg"] = "Errore di connessione al database.";
        echo json_encode($ret);
        die();
    }

    // Prendo le attività anime di tutti gli utenti
    $sql = "SELECT u.username, aa.titolo, aa.riferimento_api, aa.episodi_visti, aa.status
            FROM attivita_anime aa
            INNER JOIN utenti u ON aa.utente_id = u.id
            ORDER BY aa.data_ora DESC";

    $result = $conn->query($sql);

    if (!$result) {
        $ret = [];
        $ret["status"] = "ERR";
        $ret["msg"] = "Errore nella query.";
        echo json_encode($ret);
        die();
    }

    $attivita = [];

    while ($riga = $result->fetch_assoc()) {
        $id_api = (int) $riga["riferimento_api"];
        $immagine = "";

        if ($id_api > 0) {
            // Preparo la chiamata GraphQL
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
                'variables' => [
                    'id' => $id_api
                ]
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
            "tipo" => "anime",
            "username" => $riga["username"],
            "titolo" => $riga["titolo"],
            "episodi_visti" => $riga["episodi_visti"],
            "status" => $riga["status"],
            "immagine" => $immagine
        ];
    }

    // Risposta finale
    $ret = [];
    $ret["status"] = "OK";
    $ret["data"] = $attivita;
    echo json_encode($ret);
    die();

?>
