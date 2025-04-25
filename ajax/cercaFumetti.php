<?php

header("Content-Type: application/json");

if (!isset($_GET['query'])) {
    echo json_encode([
        "status" => "ERR",
        "msg" => "Parametro 'query' mancante"
    ]);
    exit;
}

$api_key = "22c2e6718a7614c00a5fd89e2a6d8a4cfe8274ce";
$query = $_GET['query'];

$url = "https://comicvine.gamespot.com/api/search/?"
     . "api_key={$api_key}"
     . "&format=json"
     . "&resources=issue"
     . "&query=" . urlencode($query);

// Impostazioni delle opzioni HTTP, incluso l'intestazione User-Agent
$options = [
    "http" => [
        "header" => "User-Agent: ComicVine PHP Client\r\n"
    ]
];

// Creazione del contesto di stream con le opzioni
$context = stream_context_create($options);

// Esegui la richiesta con il contesto di stream
$response = file_get_contents($url, false, $context);

if ($response === FALSE) {
    echo json_encode([
        "status" => "ERR",
        "msg" => "Errore nella richiesta a ComicVine"
    ]);
    exit;
}

$data = json_decode($response, true);
$results = [];

foreach ($data['results'] as $issue) {
    $titolo = "Senza titolo";
    if (isset($issue['name']) && $issue['name'] !== "") {
        $titolo = $issue['name'];
    }

    $volume = "Serie sconosciuta";
    if (isset($issue['volume']['name'])) {
        $volume = $issue['volume']['name'];
    }

    $numero = "N/A";
    if (isset($issue['issue_number'])) {
        $numero = $issue['issue_number'];
    }

    $descrizione = "";
    if (isset($issue['deck'])) {
        $descrizione = strip_tags($issue['deck']);
    }

    $link = "";
    if (isset($issue['site_detail_url'])) {
        $link = $issue['site_detail_url'];
    }

    $immagine = "";
    if (isset($issue['image']['small_url'])) {
        $immagine = $issue['image']['small_url'];
    }

    $results[] = [
        "titolo" => $titolo,
        "volume" => $volume,
        "numero" => $numero,
        "descrizione" => $descrizione,
        "link" => $link,
        "immagine" => $immagine
    ];
}

echo json_encode([
    "status" => "OK",
    "dati" => $results
]);

?>
