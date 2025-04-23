<?php

    session_start();

    if (!isset($_SESSION["utente_id"])) {
        $ret = [];
        $ret["status"] = "ERR";
        $ret["msg"] = "Utente non autenticato";
        echo json_encode($ret);
        die();
    }

    if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
        $ret = [];
        $ret["status"] = "ERR";
        $ret["msg"] = "Devi passare il parametro id valido";
        echo json_encode($ret);
        die();
    }

    $id = (int) $_GET["id"];

    $query = '
    query {
    Media(id: ' . $id . ', type: ANIME) {
        id
        title {
        romaji
        english
        }
        coverImage {
        large
        }
        description(asHtml: false)
        episodes
        averageScore
        genres
        tags {
        name
        isMediaSpoiler
        isGeneralSpoiler
        }
        season
        seasonYear
        startDate {
        year
        month
        day
        }
        endDate {
        year
        month
        day
        }
        characters(role: MAIN, sort: [ROLE, RELEVANCE]) {
        nodes {
            name {
            full
            }
            image {
            large
            }
        }
        }
        staff(sort: [RELEVANCE, ROLE]) {
        nodes {
            name {
            full
            }
            primaryOccupations
        }
        }
        studios {
        nodes {
            name
        }
        }
        relations {
        edges {
            relationType
            node {
            id
            title {
                romaji
            }
            type
            }
        }
        }
        recommendations(sort: RATING_DESC, page: 1, perPage: 5) {
        nodes {
            mediaRecommendation {
            id
            title {
                romaji
            }
            }
        }
        }
    }
    }
    ';

    $postData = json_encode(['query' => $query]);

    $options = [
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-type: application/json\r\nAccept: application/json\r\n",
            'content' => $postData
        ]
    ];

    $context = stream_context_create($options);
    $url = 'https://graphql.anilist.co';

    $response = file_get_contents($url, false, $context);

    if (!$response) {
        $ret = [];
        $ret["status"] = "ERR";
        $ret["msg"] = "Errore nella richiesta ad AniList";
        echo json_encode($ret);
        die();
    }

    $data = json_decode($response, true);

    if (!isset($data["data"]["Media"])) {
        $ret = [];
        $ret["status"] = "ERR";
        $ret["msg"] = "Anime non trovato";
        echo json_encode($ret);
        die();
    }

    $anime = $data["data"]["Media"];
    $info = [];

    // Titolo
    if (isset($anime["title"]["romaji"])) {
        $info["titolo"] = $anime["title"]["romaji"];
    } else {
        $info["titolo"] = "Senza titolo";
    }

    // Immagine
    if (isset($anime["coverImage"]["large"])) {
        $info["immagine"] = $anime["coverImage"]["large"];
    } else {
        $info["immagine"] = "";
    }

    // Descrizione
    if (isset($anime["description"])) {
        $info["descrizione"] = strip_tags($anime["description"]);
    } else {
        $info["descrizione"] = "";
    }

    // Episodi
    if (isset($anime["episodes"])) {
        $info["episodi"] = $anime["episodes"];
    } else {
        $info["episodi"] = "?";
    }

    // Punteggio
    if (isset($anime["averageScore"])) {
        $info["punteggio"] = $anime["averageScore"];
    } else {
        $info["punteggio"] = "N/A";
    }

    // Generi
    if (isset($anime["genres"]) && is_array($anime["genres"])) {
        $info["generi"] = implode(", ", $anime["genres"]);
    } else {
        $info["generi"] = "";
    }

    // Tags
    $info["tags"] = [];
    if (isset($anime["tags"])) {
        foreach ($anime["tags"] as $tag) {
            if (isset($tag["name"])) {
                $spoiler = false;
                if ((isset($tag["isMediaSpoiler"]) && $tag["isMediaSpoiler"]) || (isset($tag["isGeneralSpoiler"]) && $tag["isGeneralSpoiler"])) {
                    $spoiler = true;
                }
                $info["tags"][] = [
                    "nome" => $tag["name"],
                    "spoiler" => $spoiler
                ];
            }
        }
    }

    // Stagione
    $stagione = "";
    if (isset($anime["season"])) {
        $stagione = $anime["season"];
    }

    $anno = "";
    if (isset($anime["seasonYear"])) {
        $anno = $anime["seasonYear"];
    }

    $info["stagione"] = $stagione . " " . $anno;

    // Data inizio
    $info["inizio"] = "";
    if (isset($anime["startDate"]["year"]) && isset($anime["startDate"]["month"]) && isset($anime["startDate"]["day"])) {
        $info["inizio"] = $anime["startDate"]["year"] . "-" . str_pad($anime["startDate"]["month"], 2, "0", STR_PAD_LEFT) . "-" . str_pad($anime["startDate"]["day"], 2, "0", STR_PAD_LEFT);
    }

    // Data fine
    $info["fine"] = "";
    if (isset($anime["endDate"]["year"]) && isset($anime["endDate"]["month"]) && isset($anime["endDate"]["day"])) {
        $info["fine"] = $anime["endDate"]["year"] . "-" . str_pad($anime["endDate"]["month"], 2, "0", STR_PAD_LEFT) . "-" . str_pad($anime["endDate"]["day"], 2, "0", STR_PAD_LEFT);
    }

    // Personaggi principali
    $info["personaggi"] = [];
    if (isset($anime["characters"]["nodes"])) {
        foreach ($anime["characters"]["nodes"] as $pg) {
            if (isset($pg["name"]["full"]) && isset($pg["image"]["large"])) {
                $info["personaggi"][] = [
                    "nome" => $pg["name"]["full"],
                    "immagine" => $pg["image"]["large"]
                ];
            }
        }
    }

    // Staff
    $info["staff"] = [];
    if (isset($anime["staff"]["nodes"])) {
        foreach ($anime["staff"]["nodes"] as $persona) {
            $ruolo = "";
            if (isset($persona["primaryOccupations"])) {
                $ruolo = implode(", ", $persona["primaryOccupations"]);
            }
            if (isset($persona["name"]["full"])) {
                $info["staff"][] = [
                    "nome" => $persona["name"]["full"],
                    "ruolo" => $ruolo
                ];
            }
        }
    }

    // Studio
    $info["studio"] = "";
    if (isset($anime["studios"]["nodes"][0]["name"])) {
        $info["studio"] = $anime["studios"]["nodes"][0]["name"];
    }

    // Relazioni
    $info["relazioni"] = [];
    if (isset($anime["relations"]["edges"])) {
        foreach ($anime["relations"]["edges"] as $relazione) {
            if (isset($relazione["node"]["id"]) && isset($relazione["node"]["title"]["romaji"]) && isset($relazione["node"]["type"]) && isset($relazione["relationType"])) {
                $info["relazioni"][] = [
                    "id" => $relazione["node"]["id"],
                    "titolo" => $relazione["node"]["title"]["romaji"],
                    "tipo" => $relazione["node"]["type"],
                    "relazione" => $relazione["relationType"]
                ];
            }
        }
    }

    // Raccomandazioni
    $info["raccomandazioni"] = [];
    if (isset($anime["recommendations"]["nodes"])) {
        foreach ($anime["recommendations"]["nodes"] as $rec) {
            if (isset($rec["mediaRecommendation"]["id"]) && isset($rec["mediaRecommendation"]["title"]["romaji"])) {
                $info["raccomandazioni"][] = [
                    "id" => $rec["mediaRecommendation"]["id"],
                    "titolo" => $rec["mediaRecommendation"]["title"]["romaji"]
                ];
            }
        }
    }

    // Risposta finale
    $ret = [];
    $ret["status"] = "OK";
    $ret["data"] = $info;
    echo json_encode($ret);
    die();
?>
