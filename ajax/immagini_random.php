<?php
    require_once("../classi/db.php");

    if (!$conn) {
        echo json_encode(array("status" => "ERR", "msg" => "Errore di connessione al database."));
        exit;
    }

    $immagini = array();
    $immagini_set = array();

    // === Funzione per aggiungere immagine se non già presente ===
    function aggiungiImmagine($url, $titolo = "", &$immagini, &$immagini_set) {
        if ($url && !isset($immagini_set[$url])) {
            $immagini[] = array(
                "immagine" => $url, 
                "titolo" => $titolo ? $titolo : "Contenuto dalla community"
            );
            $immagini_set[$url] = true;
        }
    }

    // === Funzione per Anilist (Anime + Manga) ===
    function getAnilistImage($id, $type) {
        $query = array(
            'query' => '
                query ($id: Int) {
                    Media(id: $id, type: ' . strtoupper($type) . ') {
                        coverImage { large }
                        title { romaji english native }
                    }
                }
            ',
            'variables' => array('id' => (int)$id)
        );
        $opts = array(
            'http' => array(
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\nAccept: application/json\r\n",
                'content' => json_encode($query)
            )
        );
        $ctx = stream_context_create($opts);
        $res = file_get_contents("https://graphql.anilist.co", false, $ctx);
        if ($res !== false) {
            $data = json_decode($res, true);
            if (isset($data['data']['Media']['coverImage']['large'])) {
                $titolo = "";
                if (isset($data['data']['Media']['title']['romaji'])) {
                    $titolo = $data['data']['Media']['title']['romaji'];
                } elseif (isset($data['data']['Media']['title']['english'])) {
                    $titolo = $data['data']['Media']['title']['english'];
                } elseif (isset($data['data']['Media']['title']['native'])) {
                    $titolo = $data['data']['Media']['title']['native'];
                }
                return array(
                    'url' => $data['data']['Media']['coverImage']['large'],
                    'titolo' => $titolo
                );
            }
        }
        return array('url' => "", 'titolo' => "");
    }

    // === ANIME ===
    $res = $conn->query("SELECT riferimento_api FROM attivita_anime ORDER BY data_ora DESC LIMIT 50");
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $result = getAnilistImage($r['riferimento_api'], 'ANIME');
            if ($result['url']) {
                aggiungiImmagine($result['url'], $result['titolo'], $immagini, $immagini_set);
            }
        }
    }

    // === MANGA ===
    $res = $conn->query("SELECT riferimento_api FROM attivita_manga ORDER BY data_ora DESC LIMIT 50");
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $result = getAnilistImage($r['riferimento_api'], 'MANGA');
            if ($result['url']) {
                aggiungiImmagine($result['url'], $result['titolo'], $immagini, $immagini_set);
            }
        }
    }

    // === FUMETTI ===
    $cv_api = "22c2e6718a7614c00a5fd89e2a6d8a4cfe8274ce";
    $res = $conn->query("SELECT riferimento_api FROM attivita_fumetto ORDER BY data_ora DESC LIMIT 50");
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $id = (int)$r["riferimento_api"];
            if ($id > 0) {
                $url = "https://comicvine.gamespot.com/api/issue/4000-" . $id . "/?api_key=" . $cv_api . "&format=json";
                $opts = array("http" => array("header" => "User-Agent: ComicVine PHP Client\r\n"));
                $ctx = stream_context_create($opts);
                $res_c = file_get_contents($url, false, $ctx);
                if ($res_c !== false) {
                    $data = json_decode($res_c, true);
                    $img = "";
                    $titolo = "";
                    
                    if (isset($data["results"]["image"]["small_url"])) {
                        $img = $data["results"]["image"]["small_url"];
                    } elseif (isset($data["results"]["image"]["original_url"])) {
                        $img = $data["results"]["image"]["original_url"];
                    }
                    
                    if (isset($data["results"]["volume"]["name"]) && isset($data["results"]["issue_number"])) {
                        $titolo = $data["results"]["volume"]["name"] . " #" . $data["results"]["issue_number"];
                    } elseif (isset($data["results"]["name"])) {
                        $titolo = $data["results"]["name"];
                    }
                    
                    if ($img) {
                        aggiungiImmagine($img, $titolo, $immagini, $immagini_set);
                    }
                }
            }
        }
    }

    // === VIDEOGIOCHI ===
    $gb_api = "bb709d6b2114c61e3f0c9999834b43918d1a2427";
    $res = $conn->query("SELECT guid FROM attivita_videogioco ORDER BY data_ora DESC LIMIT 50");
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $guid = $r["guid"];
            if ($guid) {
                $url = "https://www.giantbomb.com/api/game/" . $guid . "/?api_key=" . $gb_api . "&format=json";
                $opts = array("http" => array("header" => "User-Agent: GiantBomb PHP Client\r\n"));
                $ctx = stream_context_create($opts);
                $res_g = file_get_contents($url, false, $ctx);
                if ($res_g !== false) {
                    $data = json_decode($res_g, true);
                    $img = "";
                    $titolo = "";
                    
                    if (isset($data["results"]["image"]["small_url"])) {
                        $img = $data["results"]["image"]["small_url"];
                    } elseif (isset($data["results"]["image"]["original_url"])) {
                        $img = $data["results"]["image"]["original_url"];
                    }
                    
                    if (isset($data["results"]["name"])) {
                        $titolo = $data["results"]["name"];
                    }
                    
                    if ($img) {
                        aggiungiImmagine($img, $titolo, $immagini, $immagini_set);
                    }
                }
            }
        }
    }

    // Aggiungiamo immagini di backup nel caso non ce ne siano abbastanza
    $backup_images = [
        ['url' => 'images/default-anime.jpg', 'titolo' => 'Anime in Evidenza'],
        ['url' => 'images/default-manga.jpg', 'titolo' => 'Manga Popolari'],
        ['url' => 'images/default-comic.jpg', 'titolo' => 'Fumetti del Mese'],
        ['url' => 'images/default-game.jpg', 'titolo' => 'Videogiochi Consigliati']
    ];
    
    // Se abbiamo meno di 8 immagini, aggiungiamo quelle di backup
    if (count($immagini) < 8) {
        foreach ($backup_images as $img) {
            aggiungiImmagine($img['url'], $img['titolo'], $immagini, $immagini_set);
            if (count($immagini) >= 8) break;
        }
    }

    // === Shuffle e Limite finale ===
    shuffle($immagini);
    $immagini = array_slice($immagini, 0, 15); // Aumentato il numero per un migliore scorrimento

    echo json_encode(array("status" => "OK", "data" => $immagini));
    exit;
?>