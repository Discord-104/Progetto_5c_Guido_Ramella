<?php

$apiKey = "bb709d6b2114c61e3f0c9999834b43918d1a2427";
$search = "Red"; // Cambia qui il nome del personaggio
$encodedSearch = urlencode($search);
$limit = 5;

// URL per cercare personaggi
$searchUrl = "https://www.giantbomb.com/api/search/?"
    . "api_key=$apiKey"
    . "&format=json"
    . "&query=$encodedSearch"
    . "&resources=character"
    . "&limit=$limit"
    . "&field_list=guid,name,deck,image,site_detail_url";

// Context HTTP con user-agent
$options = [
    "http" => [
        "header" => "User-Agent: GiantBomb PHP Script\r\n"
    ]
];
$context = stream_context_create($options);

// Chiamata iniziale
$response = file_get_contents($searchUrl, false, $context);
$data = json_decode($response, true);

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Personaggi: <?= htmlspecialchars($search) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        .character {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        img {
            max-width: 100px;
            border-radius: 8px;
        }
        h2 {
            margin: 0;
        }
        .info {
            font-size: 0.9em;
            color: #555;
        }
    </style>
</head>
<body>

<h1>Risultati per personaggio: "<?= htmlspecialchars($search) ?>"</h1>

<?php
if (isset($data['results']) && count($data['results']) > 0) {
    foreach ($data['results'] as $char) {
        $guid = $char['guid'];
        $detailUrl = "https://www.giantbomb.com/api/character/$guid/?api_key=$apiKey&format=json&field_list=first_appeared_in_game,gender";

        $detailResponse = file_get_contents($detailUrl, false, $context);
        $detailData = json_decode($detailResponse, true);
        $firstGame = $detailData['results']['first_appeared_in_game']['name'] ?? "N/D";
        $gender = $detailData['results']['gender'] ?? "N/D";

        $name = htmlspecialchars($char['name']);
        $deck = htmlspecialchars($char['deck'] ?? "Nessuna descrizione.");
        $img = htmlspecialchars($char['image']['small_url'] ?? "");
        $url = htmlspecialchars($char['site_detail_url']);

        echo "<div class='character'>";
        if ($img) echo "<img src='$img' alt='Immagine di $name'>";
        echo "<div>";
        echo "<h2>$name</h2>";
        echo "<p>$deck</p>";
        echo "<p class='info'><strong>Prima apparizione:</strong> $firstGame</p>";
        echo "<p class='info'><strong>Genere:</strong> $gender</p>";
        echo "<p><a href='$url' target='_blank'>Vedi su Giant Bomb</a></p>";
        echo "</div>";
        echo "</div>";
    }
} else {
    echo "<p>Nessun personaggio trovato per \"$search\".</p>";
}
?>

</body>
</html>
