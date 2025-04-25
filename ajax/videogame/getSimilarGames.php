<?php
    session_start();
    require_once ("api_helpers.php");

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

    // Get similar games
    $url = "https://www.giantbomb.com/api/game/$guid/?" .
        "api_key=" . $apiKey .
        "&format=json" .
        "&field_list=similar_games";

    $data = fetchFromApi($url);
    $similar_games = [];

    if ($data !== null && isset($data["results"]["similar_games"]) && is_array($data["results"]["similar_games"])) {
        
        foreach ($data["results"]["similar_games"] as $similar) {
            
            if (isset($similar["api_detail_url"])) {
                $url = $similar["api_detail_url"] . "?api_key=" . $apiKey . "&format=json&field_list=name,image";
                $simData = fetchFromApi($url);
                
                if ($simData !== null && isset($simData["results"])) {
                    if (isset($simData["results"]["name"])) {
                        $nome = $simData["results"]["name"];
                    } else {
                        $nome = "N/D";
                    }

                    if (isset($simData["results"]["image"]["small_url"])) {
                        $img = $simData["results"]["image"]["small_url"];
                    } else {
                        $img = "";
                    }
                    
                    $similar_games[] = ["nome" => $nome, "immagine" => $img];
                }
            }
        }
    }

    // Return results
    $ret = ["status" => "OK", "similar_games" => $similar_games];
    echo json_encode($ret);
    die();
?>
