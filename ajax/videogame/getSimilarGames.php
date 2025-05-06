<?php
    session_start();
    require_once("api_helpers.php");

    // Controllo autenticazione
    if (!isset($_SESSION["utente_id"])) {
        $ret = ["status" => "ERR", "msg" => "Utente non autenticato."];
        echo json_encode($ret);
        die();
    }

    // Controllo parametro
    if (!isset($_GET["guid"])) {
        $ret = ["status" => "ERR", "msg" => "Parametro guid mancante."];
        echo json_encode($ret);
        die();
    }

    $guid = $_GET["guid"];

    // API: ottieni giochi simili
    $url = "https://www.giantbomb.com/api/game/$guid/?"
        . "api_key=" . $apiKey
        . "&format=json"
        . "&field_list=similar_games";

    $data = fetchFromApi($url);
    $similar_games = [];

    if ($data !== null && isset($data["results"]["similar_games"]) && is_array($data["results"]["similar_games"])) {
        foreach ($data["results"]["similar_games"] as $similar) {

            if (isset($similar["api_detail_url"])) {
                // Estrai il GUID dall'URL
                $url_pezzi = explode("/game/", $similar["api_detail_url"]);
                $guid_simile = null;
                if (isset($url_pezzi[1])) {
                    $guid_simile = rtrim($url_pezzi[1], '/');
                }

                if ($guid_simile !== null) {
                    // Aspetta per evitare rate-limit
                    sleep(1);

                    // Ottieni info nome e immagine
                    $url_dettagli = $similar["api_detail_url"] . "?api_key=" . $apiKey . "&format=json&field_list=name,image";
                    $simData = fetchFromApi($url_dettagli);

                    if ($simData !== null && isset($simData["results"])) {
                        $nome = "N/D";
                        if (isset($simData["results"]["name"])) {
                            $nome = $simData["results"]["name"];
                        }

                        $img = "";
                        if (isset($simData["results"]["image"]["small_url"])) {
                            $img = $simData["results"]["image"]["small_url"];
                        }

                        // Aggiungi alla lista finale
                        $similar_games[] = [
                            "nome" => $nome,
                            "immagine" => $img,
                            "guid" => $guid_simile
                        ];
                    }
                }
            }
        }
    }

    // Risposta JSON
    $ret = ["status" => "OK", "similar_games" => $similar_games];
    echo json_encode($ret);
    die();
?>
