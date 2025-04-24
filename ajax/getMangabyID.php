<?php

session_start();

// Verifica che l'utente sia autenticato
if (!isset($_SESSION["utente_id"])) {
    $ret = [];
    $ret["status"] = "ERR";
    $ret["msg"] = "Utente non autenticato";
    echo json_encode($ret);
    die();
}

// Verifica che sia presente un ID valido
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    $ret = [];
    $ret["status"] = "ERR";
    $ret["msg"] = "Devi passare il parametro id valido";
    echo json_encode($ret);
    die();
}

$id = (int) $_GET["id"];

// Query GraphQL per ottenere i dettagli del manga
$query = '
query {
    Media(id: ' . $id . ', type: MANGA) {
        id
        title {
            romaji
            english
        }
        coverImage {
            large
        }
        description(asHtml: false)
        chapters
        averageScore
        genres
        tags {
            name
            isMediaSpoiler
        }
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
                image {
                    large
                }
                primaryOccupations
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
                    coverImage {
                        large
                    }
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
                    coverImage {
                        large
                    }
                }
            }
        }
        trailer {
            id
            site
            thumbnail
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
    $ret["msg"] = "Manga non trovato";
    echo json_encode($ret);
    die();
}

$manga = $data["data"]["Media"];
$info = [];

// Titolo
if (isset($manga["title"]["romaji"])) {
    $info["titolo"] = $manga["title"]["romaji"];
} else {
    $info["titolo"] = "Senza titolo";
}

// Immagine di copertura
if (isset($manga["coverImage"]["large"])) {
    $info["immagine"] = $manga["coverImage"]["large"];
} else {
    $info["immagine"] = "";
}

// Descrizione
if (isset($manga["description"])) {
    $info["descrizione"] = strip_tags($manga["description"]);
} else {
    $info["descrizione"] = "";
}

// Capitoli
if (isset($manga["chapters"])) {
    $info["capitoli"] = $manga["chapters"];
} else {
    $info["capitoli"] = "?";
}

// Punteggio
if (isset($manga["averageScore"])) {
    $info["punteggio"] = $manga["averageScore"];
} else {
    $info["punteggio"] = "N/A";
}

// Generi
if (isset($manga["genres"]) && is_array($manga["genres"])) {
    $info["generi"] = implode(", ", $manga["genres"]);
} else {
    $info["generi"] = "";
}

// Tags
$info["tags"] = [];
if (isset($manga["tags"])) {
    foreach ($manga["tags"] as $tag) {
        if (isset($tag["name"])) {
            $info["tags"][] = $tag["name"];
        }
    }
}

// Inizio
if (isset($manga["startDate"]["year"]) && isset($manga["startDate"]["month"]) && isset($manga["startDate"]["day"])) {
    $info["inizio"] = $manga["startDate"]["year"] . "-" . str_pad($manga["startDate"]["month"], 2, "0", STR_PAD_LEFT) . "-" . str_pad($manga["startDate"]["day"], 2, "0", STR_PAD_LEFT);
} else {
    $info["inizio"] = "";
}

// Fine
if (isset($manga["endDate"]["year"]) && isset($manga["endDate"]["month"]) && isset($manga["endDate"]["day"])) {
    $info["fine"] = $manga["endDate"]["year"] . "-" . str_pad($manga["endDate"]["month"], 2, "0", STR_PAD_LEFT) . "-" . str_pad($manga["endDate"]["day"], 2, "0", STR_PAD_LEFT);
} else {
    $info["fine"] = "?";
}

// Personaggi principali
$info["personaggi"] = [];
if (isset($manga["characters"]["nodes"])) {
    foreach ($manga["characters"]["nodes"] as $pg) {
        if (isset($pg["name"]["full"])) {
            $nome = $pg["name"]["full"];
        } else {
            $nome = "";
        }

        if (isset($pg["image"]["large"])) {
            $immagine = $pg["image"]["large"];
        } else {
            $immagine = "";
        }

        $info["personaggi"][] = [
            "nome" => $nome,
            "immagine" => $immagine
        ];
    }
}

// Staff
$info["staff"] = [];
if (isset($manga["staff"]["nodes"])) {
    foreach ($manga["staff"]["nodes"] as $persona) {
        if (isset($persona["name"]["full"])) {
            $nome = $persona["name"]["full"];
        } else {
            $nome = "";
        }

        if (isset($persona["primaryOccupations"])) {
            $ruolo = implode(", ", $persona["primaryOccupations"]);
        } else {
            $ruolo = "";
        }

        if (isset($persona["image"]["large"])) {
            $immagine = $persona["image"]["large"];
        } else {
            $immagine = "";
        }

        $info["staff"][] = [
            "nome" => $nome,
            "ruolo" => $ruolo,
            "immagine" => $immagine
        ];
    }
}

// Relazioni
$info["relazioni"] = [];
if (isset($manga["relations"]["edges"])) {
    foreach ($manga["relations"]["edges"] as $relazione) {
        if (isset($relazione["node"]["id"])) {
            $id = $relazione["node"]["id"];
        } else {
            $id = "";
        }

        if (isset($relazione["node"]["title"]["romaji"])) {
            $titolo = $relazione["node"]["title"]["romaji"];
        } else {
            $titolo = "";
        }

        if (isset($relazione["node"]["type"])) {
            $tipo = $relazione["node"]["type"];
        } else {
            $tipo = "";
        }

        if (isset($relazione["relationType"])) {
            $relazioneTipo = $relazione["relationType"];
        } else {
            $relazioneTipo = "";
        }

        if (isset($relazione["node"]["coverImage"]["large"])) {
            $immagine = $relazione["node"]["coverImage"]["large"];
        } else {
            $immagine = "";
        }

        $info["relazioni"][] = [
            "id" => $id,
            "titolo" => $titolo,
            "tipo" => $tipo,
            "relazione" => $relazioneTipo,
            "immagine" => $immagine
        ];
    }
}

// Raccomandazioni
$info["raccomandazioni"] = [];
if (isset($manga["recommendations"]["nodes"])) {
    foreach ($manga["recommendations"]["nodes"] as $rec) {
        if (isset($rec["mediaRecommendation"]["id"])) {
            $id = $rec["mediaRecommendation"]["id"];
        } else {
            $id = "";
        }

        if (isset($rec["mediaRecommendation"]["title"]["romaji"])) {
            $titolo = $rec["mediaRecommendation"]["title"]["romaji"];
        } else {
            $titolo = "";
        }

        if (isset($rec["mediaRecommendation"]["coverImage"]["large"])) {
            $immagine = $rec["mediaRecommendation"]["coverImage"]["large"];
        } else {
            $immagine = "";
        }

        $info["raccomandazioni"][] = [
            "id" => $id,
            "titolo" => $titolo,
            "immagine" => $immagine
        ];
    }
}

// Trailer (se presente)
if (isset($manga["trailer"]["thumbnail"])) {
    if (isset($manga["trailer"]["id"])) {
        $id = $manga["trailer"]["id"];
    } else {
        $id = "";
    }

    if (isset($manga["trailer"]["site"])) {
        $site = $manga["trailer"]["site"];
    } else {
        $site = "";
    }

    if (isset($manga["trailer"]["thumbnail"])) {
        $thumbnail = $manga["trailer"]["thumbnail"];
    } else {
        $thumbnail = "";
    }

    $info["trailer"] = [
        "id" => $id,
        "site" => $site,
        "thumbnail" => $thumbnail
    ];
} else {
    $info["trailer"] = null;
}

$ret = [];
$ret["status"] = "OK";
$ret["data"] = $info;
echo json_encode($ret);
die();


?>
