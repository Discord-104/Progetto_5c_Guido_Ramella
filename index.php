<?php

// URL API GraphQL
$url = 'https://graphql.anilist.co';

// Query GraphQL
$query = '
    query {
        Page(perPage: 10) {
            media(type: ANIME, sort: POPULARITY_DESC) {
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

// Prepara i dati in JSON
$postData = json_encode(['query' => $query]);

// Prepara intestazioni e contenuto come stringa HTTP
$headers = implode("\r\n", [
    "Content-type: application/json",
    "Accept: application/json"
]);

$context = stream_context_create([
    'http' => [
        'method'  => 'POST',
        'header'  => $headers,
        'content' => $postData
    ]
]);

// Richiesta HTTP
$response = file_get_contents($url, false, $context);

// Decodifica
$data = json_decode($response, true);

// Visualizzazione
if (isset($data['data']['Page']['media'])) {
    echo "<h2>Anime popolari:</h2>";
    echo "<div style='display: flex; flex-wrap: wrap; gap: 20px;'>";

    foreach ($data['data']['Page']['media'] as $anime) {
        $title = $anime['title']['romaji'] ?? 'Senza titolo';
        $image = $anime['coverImage']['large'] ?? '';
        $url = $anime['siteUrl'] ?? '#';
        $desc = strip_tags($anime['description'] ?? '');
        $desc = strlen($desc) > 180 ? substr($desc, 0, 180) . '...' : $desc;
        $episodes = $anime['episodes'] ?? '?';

        echo "<div style='width: 200px; text-align: center;'>";
        echo "<a href='$url' target='_blank'>";
        echo "<img src='$image' alt='$title' style='width: 100%; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.2);'>";
        echo "</a>";
        echo "<div style='margin-top: 8px; font-weight: bold;'>$title</div>";
        echo "<div style='font-size: 14px; margin: 5px 0;'>Episodi: $episodes</div>";
        echo "<div style='font-size: 13px; color: #555;'>$desc</div>";
        echo "</div>";
    }

    echo "</div>";
} else {
    echo "Errore nella risposta o nessun anime trovato.";
}
?>
