<?php
    session_start();
    require_once("../classi/db.php");

    if (!isset($_SESSION["utente_id"])) {
        echo json_encode([
            "status" => "ERR",
            "msg"    => "Utente non autenticato"
        ]);
        die();
    }

    $utente_id = $_SESSION["utente_id"];
    $api_key_comicvine = "22c2e6718a7614c00a5fd89e2a6d8a4cfe8274ce";
    $api_key_giantbomb = "bb709d6b2114c61e3f0c9999834b43918d1a2427";

    // Funzione per sommare un campo
    function sommaCampo($table, $campo, $utente_id) {
        global $conn;

        $query = "SELECT SUM($campo) as totale FROM $table WHERE utente_id = ?";
        $stmt  = $conn->prepare($query);
        $stmt->bind_param("i", $utente_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if ($result && $result['totale']) {
            return (int)$result['totale'];
        } else {
            return 0;
        }
    }

    // Funzioni API
    function getImmagineAnilist($id, $type) {
        $query = [
            'query' => '
                query ($id: Int) {
                    Media(id: $id, type: ' . strtoupper($type) . ') {
                        coverImage {
                            large
                        }
                    }
                }
            ',
            'variables' => ['id' => $id]
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
                return $data['data']['Media']['coverImage']['large'];
            }
        }

        return "";
    }

    function getImmagineComicVine($id) {
        global $api_key_comicvine;

        $url = "https://comicvine.gamespot.com/api/issue/4000-" . $id . "/?api_key=" . $api_key_comicvine . "&format=json";
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
                return $data["results"]["image"]["small_url"];
            } else if (isset($data["results"]["image"]["original_url"])) {
                return $data["results"]["image"]["original_url"];
            }
        }

        return "";
    }

    function getImmagineGiantBomb($guid) {
        global $api_key_giantbomb;

        $url = "https://www.giantbomb.com/api/game/" . $guid . "/?api_key=" . $api_key_giantbomb . "&format=json";
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
                return $data["results"]["image"]["small_url"];
            } else if (isset($data["results"]["image"]["original_url"])) {
                return $data["results"]["image"]["original_url"];
            }
        }

        return "";
    }

    // Preferiti con immagine
    function getPreferitiConImmagine($table, $utente_id, $tipo) {
        global $conn;
        
        $query = "";
        
        if ($tipo === "videogioco") {
            $query = "SELECT titolo, guid FROM $table WHERE utente_id = ? AND preferito = 1";
        } else if ($tipo === "fumetto") {
            $query = "SELECT titolo, riferimento_api, nome_volume, numero_fumetto FROM $table WHERE utente_id = ? AND preferito = 1";
        } else {
            $query = "SELECT titolo, riferimento_api FROM $table WHERE utente_id = ? AND preferito = 1";
        }
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $utente_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $preferiti = [];

        while ($row = $result->fetch_assoc()) {
            $immagine = "";

            if (($tipo === "anime" || $tipo === "manga") && !empty($row["riferimento_api"])) {
                $immagine = getImmagineAnilist((int)$row["riferimento_api"], $tipo);
            } else if ($tipo === "fumetto" && !empty($row["riferimento_api"])) {
                $immagine = getImmagineComicVine((int)$row["riferimento_api"]);
            } else if ($tipo === "videogioco" && !empty($row["guid"])) {
                $immagine = getImmagineGiantBomb($row["guid"]);
            }

            $preferito = [
                "titolo"   => $row["titolo"],
                "immagine" => $immagine
            ];
            
            // Aggiungi campi specifici per fumetti
            if ($tipo === "fumetto") {
                if (isset($row["nome_volume"])) {
                    $preferito["nome_volume"] = $row["nome_volume"];
                }
                if (isset($row["numero_fumetto"])) {
                    $preferito["numero_fumetto"] = $row["numero_fumetto"];
                }
            }

            $preferiti[] = $preferito;
        }

        return $preferiti;
    }

    // Immagine profilo
    function getImmagineProfilo($utente_id) {
        global $conn;

        $query = "SELECT profile_image FROM utenti WHERE id = ?";
        $stmt  = $conn->prepare($query);
        $stmt->bind_param("i", $utente_id);
        $stmt->execute();

        $result = $stmt->get_result()->fetch_assoc();
        
        if (isset($result['profile_image'])) {
            return $result['profile_image'];
        } else {
            return null;
        }
    }

    // Calcolo statistiche
    $episodi_visti   = sommaCampo('attivita_anime',     'episodi_visti',   $utente_id);
    $capitoli_letti  = sommaCampo('attivita_manga',     'capitoli_letti',  $utente_id);
    $volumi_letti    = sommaCampo('attivita_manga',     'volumi_letti',    $utente_id);
    $pagine_lette    = sommaCampo('attivita_fumetto',   'numero_letti',    $utente_id);
    $ore_giocate     = sommaCampo('attivita_videogioco','ore_giocate',     $utente_id);

    // Preferiti
    $preferiti_anime       = getPreferitiConImmagine('attivita_anime',      $utente_id, "anime");
    $preferiti_manga       = getPreferitiConImmagine('attivita_manga',      $utente_id, "manga");
    $preferiti_fumetti     = getPreferitiConImmagine('attivita_fumetto',    $utente_id, "fumetto");
    $preferiti_videogiochi = getPreferitiConImmagine('attivita_videogioco', $utente_id, "videogioco");

    $immagine_profilo = getImmagineProfilo($utente_id);

    // Risposta finale
    echo json_encode([
        "status" => "OK",
        "msg"    => "Dati caricati correttamente",
        "data"   => [
            "episodi_visti"         => $episodi_visti,
            "capitoli_letti"        => $capitoli_letti,
            "volumi_letti"          => $volumi_letti,
            "pagine_lette"          => $pagine_lette,
            "ore_giocate"           => $ore_giocate,
            "preferiti_anime"       => $preferiti_anime,
            "preferiti_manga"       => $preferiti_manga,
            "preferiti_fumetti"     => $preferiti_fumetti,
            "preferiti_videogiochi" => $preferiti_videogiochi,
            "immagine_profilo"      => $immagine_profilo
        ]
    ]);
    die();
?>