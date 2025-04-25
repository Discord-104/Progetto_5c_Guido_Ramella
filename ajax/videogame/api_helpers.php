<?php
    $apiKey = "bb709d6b2114c61e3f0c9999834b43918d1a2427";

    // Function for API calls with proper headers
    function fetchFromApi($url) {
        $options = [
            "http" => [
                "method" => "GET",
                "header" => "User-Agent: GiantBomb PHP Script\r\n"
            ]
        ];
        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        if ($response === false) {
            return null;
        }
        return json_decode($response, true);
    }

    // Extract names from arrays
    function extractNames($array) {
        $result = [];
        if (is_array($array)) {
            foreach ($array as $item) {
                if (isset($item["name"])) {
                    $result[] = $item["name"];
                }
            }
        }
        return $result;
    }

    // Get basic game information without related data
    function getBasicGameData($guid) {
        global $apiKey;
        
        $url = "https://www.giantbomb.com/api/game/$guid/?" .
            "api_key=" . $apiKey .
            "&format=json" .
            "&field_list=name,deck,genres,platforms,original_release_date,dlcs,image,developers,publishers,themes,franchises,aliases";
        
        $data = fetchFromApi($url);
        
        if ($data === null || !isset($data["results"])) {
            return null;
        }
        
        $game = $data["results"];
        $ret = [];
        $ret["status"] = "OK";
        $ret["dato"] = [];
        
        // Basic information
        if (isset($game["name"])) {
            $ret["dato"]["nome"] = $game["name"];
        } else {
            $ret["dato"]["nome"] = "N/D";
        }

        if (isset($game["deck"])) {
            $ret["dato"]["descrizione"] = $game["deck"];
        } else {
            $ret["dato"]["descrizione"] = "Nessuna descrizione disponibile.";
        }

        if (isset($game["genres"])) {
            $ret["dato"]["generi"] = extractNames($game["genres"]);
        } else {
            $ret["dato"]["generi"] = [];
        }

        if (isset($game["platforms"])) {
            $ret["dato"]["piattaforme"] = extractNames($game["platforms"]);
        } else {
            $ret["dato"]["piattaforme"] = [];
        }

        if (isset($game["original_release_date"])) {
            $ret["dato"]["data_uscita"] = $game["original_release_date"];
        } else {
            $ret["dato"]["data_uscita"] = "N/D";
        }

        if (isset($game["dlcs"])) {
            $ret["dato"]["dlc"] = extractNames($game["dlcs"]);
        } else {
            $ret["dato"]["dlc"] = [];
        }

        if (isset($game["image"]["small_url"])) {
            $ret["dato"]["immagine"] = $game["image"]["small_url"];
        } else {
            $ret["dato"]["immagine"] = "";
        }

        if (isset($game["developers"])) {
            $ret["dato"]["sviluppatori"] = extractNames($game["developers"]);
        } else {
            $ret["dato"]["sviluppatori"] = [];
        }

        if (isset($game["publishers"])) {
            $ret["dato"]["publisher"] = extractNames($game["publishers"]);
        } else {
            $ret["dato"]["publisher"] = [];
        }

        if (isset($game["themes"])) {
            $ret["dato"]["temi"] = extractNames($game["themes"]);
        } else {
            $ret["dato"]["temi"] = [];
        }

        if (isset($game["franchises"])) {
            $ret["dato"]["franchises"] = extractNames($game["franchises"]);
        } else {
            $ret["dato"]["franchises"] = [];
        }

        if (isset($game["aliases"])) {
            $ret["dato"]["aliases"] = $game["aliases"];
        } else {
            $ret["dato"]["aliases"] = "";
        }
        
        // Placeholder for similar games and characters that will be loaded separately
        $ret["dato"]["giochi_simili"] = [];
        $ret["dato"]["personaggi"] = [];
        
        return $ret;
    }
?>
