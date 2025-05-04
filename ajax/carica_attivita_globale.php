<?php

require_once("../classi/db.php");
session_start();

if (!isset($_SESSION["utente_id"])) {
    echo json_encode(["status" => "ERR", "msg" => "Non sei loggato."]);
    die();
}

if (!$conn) {
    echo json_encode(["status" => "ERR", "msg" => "Errore di connessione al database."]);
    die();
}

$attivita = [];

// === ANIME ===
$sql_anime = "SELECT u.username, aa.titolo, aa.riferimento_api, aa.episodi_visti, aa.status, aa.anno_uscita, aa.formato
              FROM attivita_anime aa
              INNER JOIN utenti u ON aa.utente_id = u.id
              ORDER BY aa.data_ora DESC";
$result_anime = $conn->query($sql_anime);

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
            "tipo" => "anime",
            "username" => $riga["username"],
            "titolo" => $riga["titolo"],
            "episodi_visti" => $riga["episodi_visti"],
            "status" => $riga["status"],
            "anno_uscita" => $riga["anno_uscita"],
            "formato" => $riga["formato"],
            "immagine" => $immagine
        ];
    }
}

// === MANGA ===
$sql_manga = "SELECT u.username, am.titolo, am.riferimento_api, am.capitoli_letti, am.status, am.anno, am.formato
              FROM attivita_manga am
              INNER JOIN utenti u ON am.utente_id = u.id
              ORDER BY am.data_ora DESC";
$result_manga = $conn->query($sql_manga);

if ($result_manga) {
    while ($riga = $result_manga->fetch_assoc()) {
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
            "tipo" => "manga",
            "username" => $riga["username"],
            "titolo" => $riga["titolo"],
            "capitoli_letti" => $riga["capitoli_letti"],
            "status" => $riga["status"],
            "anno" => $riga["anno"],
            "formato" => $riga["formato"],
            "immagine" => $immagine
        ];
    }
}

// === FUMETTI ===
$api_key = "22c2e6718a7614c00a5fd89e2a6d8a4cfe8274ce";

$sql_fumetti = "SELECT u.username, af.titolo, af.riferimento_api, af.numero_letti, af.status, af.anno_uscita, af.nome_volume, af.numero_fumetto
                FROM attivita_fumetto af
                INNER JOIN utenti u ON af.utente_id = u.id
                ORDER BY af.data_ora DESC";
$result_fumetti = $conn->query($sql_fumetti);

if ($result_fumetti) {
    while ($riga = $result_fumetti->fetch_assoc()) {
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
                if (isset($data["results"]["image"]["small_url"])) {
                    $immagine = $data["results"]["image"]["small_url"];
                } elseif (isset($data["results"]["image"]["original_url"])) {
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
            "anno_uscita" => $riga["anno_uscita"],
            "nome_volume" => $riga["nome_volume"],
            "numero_fumetto" => $riga["numero_fumetto"],
            "immagine" => $immagine
        ];
    }
}

// === VIDEOGIOCHI ===
$api_key_videogiochi = "bb709d6b2114c61e3f0c9999834b43918d1a2427";

$sql_videogiochi = "SELECT u.username, av.titolo, av.guid, av.ore_giocate, av.status, av.data_uscita
                    FROM attivita_videogioco av
                    INNER JOIN utenti u ON av.utente_id = u.id
                    ORDER BY av.data_ora DESC";
$result_videogiochi = $conn->query($sql_videogiochi);

if ($result_videogiochi) {
    while ($riga = $result_videogiochi->fetch_assoc()) {
        $guid = $riga["guid"];
        $immagine = "";

        if (!empty($guid)) {
            $url = "https://www.giantbomb.com/api/game/" . $guid . "/?api_key=" . $api_key_videogiochi . "&format=json";
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
            "ore_giocate" => $riga["ore_giocate"],
            "status" => $riga["status"],
            "data_uscita" => $riga["data_uscita"],
            "immagine" => $immagine
        ];
    }
}

// Output finale
echo json_encode(["status" => "OK", "data" => $attivita]);
die();