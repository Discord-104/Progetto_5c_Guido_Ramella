<?php
    session_start();
    require_once("api_helpers.php");

    // Authentication check
    if (!isset($_SESSION["utente_id"])) {
        $ret = ["status" => "ERR", "msg" => "Utente non autenticato."];
        echo json_encode($ret);
        die();
    }

    // Validate parameters
    if (!isset($_GET["guid"])) {
        $ret = ["status" => "ERR", "msg" => "Parametro guid mancante."];
        echo json_encode($ret);
        die();
    }

    $guid = $_GET["guid"];

    // Get characters
    $url = "https://www.giantbomb.com/api/game/$guid/?" .
        "api_key=" . $apiKey .
        "&format=json" .
        "&field_list=characters";

    $data = fetchFromApi($url);
    $characters = [];


    foreach ($data["results"]["characters"] as $char) {

        if (isset($char["api_detail_url"])) {
            // Sleep prima della richiesta per rispettare il rate limit
            sleep(1);

            $url = $char["api_detail_url"] . "?api_key=" . $apiKey . "&format=json&field_list=name,image";
            $charData = fetchFromApi($url);

            if ($charData !== null && isset($charData["results"])) {
                if (isset($charData["results"]["name"])) {
                    $nome = $charData["results"]["name"];
                } else {
                    $nome = "N/D";
                }

                if (isset($charData["results"]["image"]["small_url"])) {
                    $img = $charData["results"]["image"]["small_url"];
                } else {
                    $img = "";
                }

                $characters[] = ["nome" => $nome, "immagine" => $img];
            }
        }
    }

    // Return results
    $ret = ["status" => "OK", "characters" => $characters];
    echo json_encode($ret);
    die();
?>
