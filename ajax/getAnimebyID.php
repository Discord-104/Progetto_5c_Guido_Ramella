<?php

    /*{
    "status": "OK"|"ERR",
    "msg": "", | "data": {...}
    }*/

    session_start();

    // controllo autenticazione
    if (!isset($_SESSION["utente_id"])) {
        $ret = [];
        $ret["status"] = "ERR";
        $ret["msg"] = "Utente non autenticato";
        echo json_encode($ret);
        die();
    }

    // controllo parametro ID
    if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
        $ret = [];
        $ret["status"] = "ERR";
        $ret["msg"] = "Devi passare il parametro id valido";
        echo json_encode($ret);
        die();
    }

    $id = (int) $_GET["id"];

    // Query GraphQL
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
                siteUrl
                description(asHtml: false)
                episodes
                averageScore
                genres
                season
                startDate {
                    year
                    month
                    day
                }
                trailer {
                    site
                    id
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

    // Costruzione dei dati
    $info = [];

    if (isset($anime["title"]["romaji"])) {
        $info["titolo"] = $anime["title"]["romaji"];
    } else {
        $info["titolo"] = "Senza titolo";
    }

    if (isset($anime["coverImage"]["large"])) {
        $info["immagine"] = $anime["coverImage"]["large"];
    } else {
        $info["immagine"] = "";
    }

    if (isset($anime["siteUrl"])) {
        $info["url"] = $anime["siteUrl"];
    } else {
        $info["url"] = "#";
    }

    if (isset($anime["description"])) {
        $info["descrizione"] = strip_tags($anime["description"]);
    } else {
        $info["descrizione"] = "";
    }

    if (isset($anime["episodes"])) {
        $info["episodi"] = $anime["episodes"];
    } else {
        $info["episodi"] = "?";
    }

    if (isset($anime["averageScore"])) {
        $info["punteggio"] = $anime["averageScore"];
    } else {
        $info["punteggio"] = "N/A";
    }

    if (isset($anime["genres"]) && is_array($anime["genres"])) {
        $info["generi"] = implode(", ", $anime["genres"]);
    } else {
        $info["generi"] = "";
    }

    if (isset($anime["season"])) {
        $info["stagione"] = $anime["season"];
    } else {
        $info["stagione"] = "";
    }

    if (isset($anime["startDate"]["year"]) && isset($anime["startDate"]["month"]) && isset($anime["startDate"]["day"])) 
    {
        // Formattazione della data in YYYY-MM-DD
        $anno = $anime["startDate"]["year"];
        $mese = str_pad($anime["startDate"]["month"], 2, "0", STR_PAD_LEFT);
        $giorno = str_pad($anime["startDate"]["day"], 2, "0", STR_PAD_LEFT);
        $info["inizio"] = "$anno-$mese-$giorno";
    } else {
        $info["inizio"] = "";
    }

    if (isset($anime["trailer"]["site"]) && $anime["trailer"]["site"] === "youtube" && isset($anime["trailer"]["id"])) 
    {
        $info["trailer"] = "https://www.youtube.com/watch?v=" . $anime["trailer"]["id"];
    } else {
        $info["trailer"] = null;
    }

    // Risposta finale
    $ret = [];
    $ret["status"] = "OK";
    $ret["data"] = $info;
    echo json_encode($ret);
    die();
?>
