<?php

// Impostazioni di base
header("Content-Type: application/json");

// URL API GraphQL
$url = 'https://graphql.anilist.co';

// Verifica che il parametro 'query' sia presente
if (!isset($_GET['query'])) {
    echo json_encode([
        "status" => "ERR",
        "msg" => "Parametro 'query' mancante"
    ]);
    exit;
}

$query = $_GET['query'];

// Query GraphQL per Anime
$queryGraphQL = '
    query {
        Page(perPage: 10) {
            media(type: ANIME, sort: POPULARITY_DESC, search: "' . $query . '") {
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

// Prepara il corpo della richiesta
$postData = json_encode(['query' => $queryGraphQL]);

// Imposta le opzioni HTTP
$options = [
    "http" => [
        "method" => "POST",
        "header" => "Content-type: application/json\r\n",
        "content" => $postData
    ]
];

// Crea il contesto di stream
$context = stream_context_create($options);

// Esegui la richiesta HTTP
$response = file_get_contents($url, false, $context);

if ($response === FALSE) {
    echo json_encode([
        "status" => "ERR",
        "msg" => "Errore nella richiesta"
    ]);
    exit;
}

// Decodifica la risposta JSON
$data = json_decode($response, true);

// Controllo dei dati
if (isset($data['data']['Page']['media'])) {
    $results = [];

    foreach ($data['data']['Page']['media'] as $anime) {
        $titolo = "Senza titolo";
        if (isset($anime['title']['romaji']) && $anime['title']['romaji'] !== "") {
            $titolo = $anime['title']['romaji'];
        }

        $image = "";
        if (isset($anime['coverImage']['large']) && $anime['coverImage']['large'] !== "") {
            $image = $anime['coverImage']['large'];
        }

        $url = "#";
        if (isset($anime['siteUrl']) && $anime['siteUrl'] !== "") {
            $url = $anime['siteUrl'];
        }

        $descrizione = "";
        if (isset($anime['description']) && $anime['description'] !== "") {
            $descrizione = strip_tags($anime['description']);
        }

        $episodi = "?";
        if (isset($anime['episodes']) && $anime['episodes'] !== "") {
            $episodi = $anime['episodes'];
        }

        $results[] = [
            'titolo' => $titolo,
            'image' => $image,
            'url' => $url,
            'descrizione' => $descrizione,
            'episodi' => $episodi
        ];
    }

    echo json_encode([
        "status" => "OK",
        "dati" => $results
    ]);
} else {
    echo json_encode([
        "status" => "ERR",
        "msg" => "Nessun anime trovato"
    ]);
}
?>
