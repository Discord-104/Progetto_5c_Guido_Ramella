<?php

$api_key = "22c2e6718a7614c00a5fd89e2a6d8a4cfe8274ce";
$query = "Spider Man"; // Modifica questa variabile per cercare un personaggio diverso

$url = "https://comicvine.gamespot.com/api/search/?"
     . "api_key={$api_key}"
     . "&format=json"
     . "&resources=character"
     . "&query=" . urlencode($query);

$options = [
    "http" => [
        "header" => "User-Agent: ComicVine PHP Client\r\n"
    ]
];

$context = stream_context_create($options);
$response = file_get_contents($url, false, $context);

if ($response === FALSE) {
    die("Errore nella richiesta API.");
}

$data = json_decode($response, true);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Risultati Comic Vine</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }
        .character {
            background-color: white;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
            display: flex;
            align-items: flex-start;
            gap: 20px;
        }
        img {
            max-width: 120px;
            border-radius: 8px;
        }
        .info {
            max-width: 600px;
        }
        .info h2 {
            margin-top: 0;
        }
    </style>
</head>
<body>

<h1>Risultati per "<?= htmlspecialchars($query) ?>"</h1>

<?php
if (isset($data['results'])) {
    foreach ($data['results'] as $character) {
        $name = htmlspecialchars($character['name']);
        $description = strip_tags($character['deck'] ?? 'N/A');
        $url = $character['site_detail_url'];
        $image = $character['image']['small_url'] ?? '';
        echo <<<HTML
        <div class="character">
            <img src="{$image}" alt="{$name}">
            <div class="info">
                <h2>{$name}</h2>
                <p>{$description}</p>
                <p><a href="{$url}" target="_blank">Vedi su Comic Vine</a></p>
            </div>
        </div>
HTML;
    }
} else {
    echo "<p>Nessun risultato trovato.</p>";
}
?>

</body>
</html>
