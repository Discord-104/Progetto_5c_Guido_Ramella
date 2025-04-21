<?php

// Endpoint di AniList
$url = 'https://graphql.anilist.co';

// Variabili da inviare (puoi modificarle)
$variables = [
    'search' => 'Vegeta', // oppure lascia null
    'isBirthday' => false,
    'asHtml' => false
];

// Query come stringa
$query = '
    query Character($asHtml: Boolean, $isBirthday: Boolean, $search: String) {
        Character(isBirthday: $isBirthday, search: $search) {
            image {
                medium
                large
            }
            name {
                full
                first
                alternativeSpoiler
                alternative
                last
                middle
                native
                userPreferred
            }
            dateOfBirth {
                day
                month
                year
            }
            description(asHtml: $asHtml)
            age
            bloodType
        }
    }
';

// Corpo della richiesta con query + variabili
$postData = json_encode([
    'query' => $query,
    'variables' => $variables
]);

// Header HTTP
$headers = implode("\r\n", [
    "Content-type: application/json",
    "Accept: application/json"
]);

// Contesto HTTP
$context = stream_context_create([
    'http' => [
        'method'  => 'POST',
        'header'  => $headers,
        'content' => $postData
    ]
]);

// Esegui la richiesta
$response = file_get_contents($url, false, $context);
$data = json_decode($response, true);

// Controllo e output
if (isset($data['data']['Character'])) {
    $char = $data['data']['Character'];

    $name = $char['name']['full'] ?? 'Senza nome';
    $native = $char['name']['native'] ?? '';
    $image = $char['image']['large'] ?? '';
    $desc = strip_tags($char['description'] ?? '');
    $desc = strlen($desc) > 300 ? substr($desc, 0, 300) . '...' : $desc;

    $dob = $char['dateOfBirth'];
    $dobFormatted = ($dob['day'] && $dob['month']) ? sprintf('%02d/%02d', $dob['day'], $dob['month']) : 'N/D';

    echo "<h2>$name</h2>";
    echo "<div style='display: flex; gap: 20px;'>";
    echo "<img src='$image' alt='$name' style='height: 300px; border-radius: 10px;'>";
    echo "<div>";
    echo "<p><strong>Nome nativo:</strong> $native</p>";
    echo "<p><strong>Data di nascita:</strong> $dobFormatted</p>";
    echo "<p><strong>Età:</strong> " . ($char['age'] ?? 'N/D') . "</p>";
    echo "<p><strong>Gruppo sanguigno:</strong> " . ($char['bloodType'] ?? 'N/D') . "</p>";
    echo "<p><strong>Descrizione:</strong><br>$desc</p>";
    echo "</div>";
    echo "</div>";
} else {
    echo "Errore nella risposta o nessun personaggio trovato.";
}
?>
