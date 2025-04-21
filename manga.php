<?php

// URL API GraphQL
$url = 'https://graphql.anilist.co';

// Query GraphQL per MANGA
$query = '
    query {
        Page(perPage: 10) {
            media(type: MANGA, sort: POPULARITY_DESC) {
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

// Prepara i dati in JSON
$postData = json_encode(['query' => $query]);

// Prepara intestazioni
$headers = implode("\r\n", [
    "Content-type: application/json",
    "Accept: application/json"
]);

// Crea contesto HTTP
$context = stream_context_create([
    'http' => [
        'method'  => 'POST',
        'header'  => $headers,
        'content' => $postData
    ]
]);

// Esegui richiesta
$response = file_get_contents($url, false, $context);

// Decodifica
$data = json_decode($response, true);

// Output
if (isset($data['data']['Page']['media'])) {
    echo "<h2>Manga popolari:</h2>";
    echo "<div style='display: flex; flex-wrap: wrap; gap: 20px;'>";

    foreach ($data['data']['Page']['media'] as $manga) {
        $title = $manga['title']['romaji'] ?? 'Senza titolo';
        $image = $manga['coverImage']['large'] ?? '';
        $url = $manga['siteUrl'] ?? '#';
        $desc = strip_tags($manga['description'] ?? '');
        $desc = strlen($desc) > 180 ? substr($desc, 0, 180) . '...' : $desc;
        $chapters = $manga['chapters'] ?? '?';

        echo "<div style='width: 200px; text-align: center;'>";
        echo "<a href='$url' target='_blank'>";
        echo "<img src='$image' alt='$title' style='width: 100%; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.2);'>";
        echo "</a>";
        echo "<div style='margin-top: 8px; font-weight: bold;'>$title</div>";
        echo "<div style='font-size: 14px; margin: 5px 0;'>Capitoli: $chapters</div>";
        echo "<div style='font-size: 13px; color: #555;'>$desc</div>";
        echo "</div>";
    }

    echo "</div>";
} else {
    echo "Errore nella risposta o nessun manga trovato.";
}
?>
