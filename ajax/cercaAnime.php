<?php
    session_start();
    header("Content-Type: application/json");

    // Controllo accesso utente
    if (!isset($_SESSION["utente_id"])) {
        echo json_encode([
            "status" => "ERR",
            "msg" => "Utente non autenticato"
        ]);
        exit;
    }

    // Controllo parametro 'query'
    if (!isset($_GET['query']) || trim($_GET['query']) === "") {
        echo json_encode([
            "status" => "ERR",
            "msg" => "Parametro 'query' mancante o vuoto"
        ]);
        exit;
    }

    $termineRicerca = addslashes($_GET['query']);

    // Query GraphQL
    $queryGraphQL = '
        query {
            Page(perPage: 10) {
                media(type: ANIME, sort: POPULARITY_DESC, search: "' . $termineRicerca . '") {
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
                }
            }
        }
    ';

    $postData = json_encode(['query' => $queryGraphQL]);

    $options = [
        "http" => [
            "method" => "POST",
            "header" => "Content-type: application/json\r\n",
            "content" => $postData
        ]
    ];

    $context = stream_context_create($options);
    $response = file_get_contents("https://graphql.anilist.co", false, $context);

    if ($response === false) {
        echo json_encode([
            "status" => "ERR",
            "msg" => "Errore nella richiesta a AniList"
        ]);
        exit;
    }

    $data = json_decode($response, true);

    if (!isset($data["data"]["Page"]["media"])) {
        echo json_encode([
            "status" => "ERR",
            "msg" => "Risposta non valida dal server AniList"
        ]);
        exit;
    }

    $animeTrovati = [];

    foreach ($data["data"]["Page"]["media"] as $anime) {
        $titolo = "Senza titolo";
        if (isset($anime["title"]["romaji"]) && $anime["title"]["romaji"] != "") {
            $titolo = $anime["title"]["romaji"];
        }

        $image = "";
        if (isset($anime["coverImage"]["large"]) && $anime["coverImage"]["large"] != "") {
            $image = $anime["coverImage"]["large"];
        }

        $url = "dettagli_anime.php";
        if (isset($anime["id"])) {
            $url = "dettagli_anime.php?id=" . $anime["id"];
        }

        $descrizione = "";
        if (isset($anime["description"]) && $anime["description"] != "") {
            $descrizione = strip_tags($anime["description"]);
        }

        $episodi = "?";
        if (isset($anime["episodes"]) && $anime["episodes"] != "") {
            $episodi = $anime["episodes"];
        }

        $animeTrovati[] = [
            "titolo" => $titolo,
            "image" => $image,
            "url" => $url,
            "descrizione" => $descrizione,
            "episodi" => $episodi
        ];
    }

    echo json_encode([
        "status" => "OK",
        "dati" => $animeTrovati
    ]);
    exit;
?>
