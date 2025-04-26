<?php

/*
{
    "status": "OK" | "ERR",
    "msg": "", | "data": {}
}
*/
session_start();

// Controllo autenticazione
if (!isset($_SESSION["utente_id"])) {
    $ret = array();
    $ret["status"] = "ERR";
    $ret["msg"] = "Utente non autenticato";
    echo json_encode($ret);
    die();
}

// Controllo parametro id
if (!isset($_GET["id"])) {
    $ret = array();
    $ret["status"] = "ERR";
    $ret["msg"] = "Devi passare il parametro id";
    echo json_encode($ret);
    die();
}

$utente_id = $_SESSION["utente_id"];
$id = $_GET["id"];

$api_key = "22c2e6718a7614c00a5fd89e2a6d8a4cfe8274ce";

// Costruzione URL manuale senza ${}
$url_issue = "https://comicvine.gamespot.com/api/issue/4000-" . $id . "/?api_key=" . $api_key . "&format=json";

$options = array(
    "http" => array(
        "header" => "User-Agent: ComicVine PHP Client\r\n"
    )
);
$context = stream_context_create($options);

$response_issue = file_get_contents($url_issue, false, $context);

if ($response_issue === false) {
    $ret = array();
    $ret["status"] = "ERR";
    $ret["msg"] = "Errore nella richiesta verso ComicVine";
    echo json_encode($ret);
    die();
}

$data_issue = json_decode($response_issue, true);

if (!isset($data_issue["results"])) {
    $ret = array();
    $ret["status"] = "ERR";
    $ret["msg"] = "Dati del fumetto non trovati";
    echo json_encode($ret);
    die();
}

$fumetto = $data_issue["results"];

// Estrazione dati principali
$titolo = "";
if (isset($fumetto["name"])) {
    $titolo = $fumetto["name"];
} else {
    $titolo = "Senza titolo";
}

$volume = "";
if (isset($fumetto["volume"]["name"])) {
    $volume = $fumetto["volume"]["name"];
} else {
    $volume = "Serie sconosciuta";
}

$numero = "";
if (isset($fumetto["issue_number"])) {
    $numero = $fumetto["issue_number"];
} else {
    $numero = "N/A";
}

$descrizione = "";
if (isset($fumetto["description"])) {
    $descrizione = strip_tags($fumetto["description"]);
} else {
    $descrizione = "Nessuna descrizione disponibile.";
}

$data_pubblicazione = "";
if (isset($fumetto["cover_date"])) {
    $data_pubblicazione = $fumetto["cover_date"];
} else {
    $data_pubblicazione = "Data sconosciuta";
}

$immagine = "";
if (isset($fumetto["image"]["small_url"])) {
    $immagine = $fumetto["image"]["small_url"];
}

$link = "";
if (isset($fumetto["site_detail_url"])) {
    $link = $fumetto["site_detail_url"];
}

$aliases = "";
if (isset($fumetto["aliases"])) {
    $aliases = $fumetto["aliases"];
}

// Recupero personaggi
$personaggi = array();
if (isset($fumetto["character_credits"])) {
    foreach ($fumetto["character_credits"] as $personaggio) {
        $nome = "";
        if (isset($personaggio["name"])) {
            $nome = $personaggio["name"];
        } else {
            $nome = "Sconosciuto";
        }

        $immagine_personaggio = "";

        // Se manca l'immagine la recupero tramite chiamata API sul dettaglio del personaggio
        if (!isset($personaggio["image"]["icon_url"]) && isset($personaggio["api_detail_url"])) {
            $url_personaggio = $personaggio["api_detail_url"] . "?api_key=" . $api_key . "&format=json";
            $response_personaggio = file_get_contents($url_personaggio, false, $context);

            if ($response_personaggio !== false) {
                $data_personaggio = json_decode($response_personaggio, true);
                if (isset($data_personaggio["results"]["image"]["icon_url"])) {
                    $immagine_personaggio = $data_personaggio["results"]["image"]["icon_url"];
                }
            }
        } else {
            if (isset($personaggio["image"]["icon_url"])) {
                $immagine_personaggio = $personaggio["image"]["icon_url"];
            }
        }

        $personaggi[] = array(
            "nome" => $nome,
            "immagine" => $immagine_personaggio
        );
    }
}

// Risposta finale
$ret = array();
$ret["status"] = "OK";
$ret["data"] = array(
    "titolo" => $titolo,
    "volume" => $volume,
    "numero" => $numero,
    "descrizione" => $descrizione,
    "data_pubblicazione" => $data_pubblicazione,
    "immagine" => $immagine,
    "link" => $link,
    "aliases" => $aliases,
    "personaggi" => $personaggi
);

echo json_encode($ret);
die();

?>
