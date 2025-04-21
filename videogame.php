<?php

$apiKey = "bb709d6b2114c61e3f0c9999834b43918d1a2427";
$search = "Final Fantasy"; // cambia qui il testo di ricerca
$encodedSearch = urlencode($search);
$limit = 5;

// URL search con filtro solo giochi
$searchUrl = "https://www.giantbomb.com/api/search/?"
    . "api_key=$apiKey"
    . "&format=json"
    . "&query=$encodedSearch"
    . "&resources=game"
    . "&limit=$limit"
    . "&field_list=guid,name,deck,image,platforms,original_release_date,site_detail_url";

// Context HTTP
$options = [
    "http" => [
        "header" => "User-Agent: GiantBomb PHP Script\r\n"
    ]
];
$context = stream_context_create($options);

$response = file_get_contents($searchUrl, false, $context);
$data = json_decode($response, true);

function formatPlatforms($platforms) {
    if (!is_array($platforms)) return "N/D";
    return implode(", ", array_map(fn($p) => $p['name'], $platforms));
}

function formatGenres($genres) {
    if (!is_array($genres)) return "N/D";
    return implode(", ", array_map(fn($g) => $g['name'], $genres));
}

function formatDate($dateStr) {
    if (!$dateStr) return "Data non disponibile";
    return date("d/m/Y", strtotime($dateStr));
}

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Risultati per "<?= htmlspecialchars($search) ?>"</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f0f0f0;
        }
        .game {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            align-items: flex-start;
            gap: 15px;
        }
        img {
            border-radius: 8px;
            max-width: 100px;
        }
        h2 {
            margin: 0 0 10px;
        }
        .info {
            font-size: 0.9em;
            color: #555;
        }
    </style>
</head>
<body>

<h1>Risultati per: "<?= htmlspecialchars($search) ?>"</h1>

<?php
if (isset($data['results']) && count($data['results']) > 0) {
    foreach ($data['results'] as $game) {
        $guid = $game['guid'];
        $detailUrl = "https://www.giantbomb.com/api/game/$guid/?"
            . "api_key=$apiKey&format=json&field_list=genres";

        // Otteniamo i generi veri
        $detailResponse = file_get_contents($detailUrl, false, $context);
        $detailData = json_decode($detailResponse, true);
        $genres = formatGenres($detailData['results']['genres'] ?? []);

        // Altri dati
        $name = htmlspecialchars($game['name']);
        $desc = htmlspecialchars($game['deck'] ?? "Nessuna descrizione.");
        $img = htmlspecialchars($game['image']['small_url'] ?? "");
        $url = htmlspecialchars($game['site_detail_url']);
        $platforms = formatPlatforms($game['platforms'] ?? []);
        $releaseDate = formatDate($game['original_release_date'] ?? null);

        echo "<div class='game'>";
        if ($img) {
            echo "<img src='$img' alt='Immagine di $name'>";
        }
        echo "<div>";
        echo "<h2>$name</h2>";
        echo "<p>$desc</p>";
        echo "<p class='info'><strong>Generi:</strong> $genres</p>";
        echo "<p class='info'><strong>Piattaforme:</strong> $platforms</p>";
        echo "<p class='info'><strong>Data di uscita:</strong> $releaseDate</p>";
        echo "<p><a href='$url' target='_blank'>Scheda su GiantBomb</a></p>";
        echo "</div>";
        echo "</div>";
    }
} else {
    echo "<p>Nessun risultato trovato per \"<strong>" . htmlspecialchars($search) . "</strong>\".</p>";
}
?>

</body>
</html>
