<?php

// Impostazioni di base
header("Content-Type: application/json");

// Endpoint di AniList
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

// Query GraphQL per Manga
$queryGraphQL = '
    query {
        Page(perPage: 10) {
            media(type: MANGA, sort: POPULARITY_DESC, search: "' . $query . '") {
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
                chapters
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

    foreach ($data['data']['Page']['media'] as $manga) {
        $titolo = "Senza titolo";
        if (isset($manga['title']['romaji']) && $manga['title']['romaji'] !== "") {
            $titolo = $manga['title']['romaji'];
        }

        $image = "";
        if (isset($manga['coverImage']['large']) && $manga['coverImage']['large'] !== "") {
            $image = $manga['coverImage']['large'];
        }

        $url = "dettagli_manga.php";
        if (isset($manga["id"])) {
            $url = "dettagli_manga.php?id=" . $manga["id"];
        }

        $descrizione = "";
        if (isset($manga['description']) && $manga['description'] !== "") {
            $descrizione = strip_tags($manga['description']);
        }

        $capitoli = "?";
        if (isset($manga['chapters']) && $manga['chapters'] !== "") {
            $capitoli = $manga['chapters'];
        }

        $results[] = [
            'titolo' => $titolo,
            'image' => $image,
            'url' => $url,
            'descrizione' => $descrizione,
            'capitoli' => $capitoli
        ];
    }

    echo json_encode([
        "status" => "OK",
        "dati" => $results
    ]);
} else {
    echo json_encode([
        "status" => "ERR",
        "msg" => "Nessun manga trovato"
    ]);
}
?>
