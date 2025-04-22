<?php

header("Content-Type: application/json");

if (!isset($_GET['query'])) {
    echo json_encode([
        "status" => "ERR",
        "msg" => "Parametro 'query' mancante"
    ]);
    exit;
}

$api_key = "bb709d6b2114c61e3f0c9999834b43918d1a2427";
$query = $_GET['query'];

$url = "https://www.giantbomb.com/api/search/?"
     . "api_key={$api_key}"
     . "&format=json"
     . "&resources=game"
     . "&query=" . urlencode($query);

// Impostazioni delle opzioni HTTP, incluso l'intestazione User-Agent
$options = [
    "http" => [
        "header" => "User-Agent: GiantBomb PHP Client\r\n"
    ]
];

// Creazione del contesto di stream con le opzioni
$context = stream_context_create($options);

// Esegui la richiesta con il contesto di stream
$response = file_get_contents($url, false, $context);

if ($response === FALSE) {
    echo json_encode([
        "status" => "ERR",
        "msg" => "Errore nella richiesta a GiantBomb"
    ]);
    exit;
}

$data = json_decode($response, true);
$results = [];

foreach ($data['results'] as $game) {
    $titolo = "Senza titolo";
    if (isset($game['name']) && $game['name'] !== "") {
        $titolo = $game['name'];
    }

    $descrizione = "";
    if (isset($game['deck'])) {
        $descrizione = strip_tags($game['deck']);
    }

    $link = "";
    if (isset($game['site_detail_url'])) {
        $link = $game['site_detail_url'];
    }

    $immagine = "";
    if (isset($game['image']['small_url'])) {
        $immagine = $game['image']['small_url'];
    }

    $results[] = [
        "titolo" => $titolo,
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
