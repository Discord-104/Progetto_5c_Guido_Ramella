<?php
    //TODO: controllo cap e volumi massimi quando si mette complete e passare il titolo con l'endpoint di anilist
    require_once("../classi/db.php");
    session_start();

    $ret = [];

    // Funzione di validazione data
    function valida_data($data) {
        $d = DateTime::createFromFormat('Y-m-d', $data);
        return $d && $d->format('Y-m-d') === $data;
    }

    // Funzione per ottenere capitoli e volumi da Anilist
    function get_capitoli_volumi_from_anilist($manga_id) {
        $ret = [];

        if (!isset($manga_id) || !preg_match('/^\d+$/', $manga_id)) {
            $ret["status"] = "ERR";
            $ret["msg"] = "ID manga mancante o non valido.";
            $ret["dato"] = null;
            return $ret;
        }

        $query = '
            query ($id: Int) {
                Media(id: $id, type: MANGA) {
                    title {
                        romaji
                    }
                    chapters
                    volumes
                }
            }
        ';

        $variables = ['id' => intval($manga_id)];

        $payload = json_encode([
            'query' => $query,
            'variables' => $variables
        ]);

        $opts = [
            "http" => [
                "method" => "POST",
                "header" => "Content-Type: application/json\r\nAccept: application/json\r\n",
                "content" => $payload
            ]
        ];

        $context = stream_context_create($opts);
        $result = file_get_contents('https://graphql.anilist.co', false, $context);

        if ($result === false) {
            $ret["status"] = "ERR";
            $ret["msg"] = "Errore nella connessione ad Anilist.";
            $ret["dato"] = null;
            return $ret;
        }

        $data = json_decode($result, true);

        if (isset($data["data"]["Media"]["chapters"]) || isset($data["data"]["Media"]["volumes"])) {
            $ret["status"] = "OK";
            $ret["msg"] = "";
            $ret["dato"] = [
                "capitoli" => $data["data"]["Media"]["chapters"],
                "volumi" => $data["data"]["Media"]["volumes"],
                "titolo" => $data["data"]["Media"]["title"]["romaji"]
            ];
        } else {
            $ret["status"] = "ERR";
            $ret["msg"] = "Informazioni non trovate su Anilist.";
            $ret["dato"] = null;
        }

        return $ret;
    }

    // --- Inizio codice principale ---

    if (isset($_GET["utente_id"]) && isset($_GET["manga_id"])) {

        $utente_id = $_GET["utente_id"];
        $manga_id = $_GET["manga_id"];

        if (!preg_match('/^\d+$/', $utente_id) || !preg_match('/^\d+$/', $manga_id)) {
            $ret["status"] = "ERROR";
            $ret["message"] = "ID utente o ID manga non valido.";
            echo json_encode($ret);
            die();
        }

        $utente_id = intval($utente_id);
        $manga_id = intval($manga_id);

        // Recuperiamo il titolo, capitoli e volumi massimi da Anilist
        $response = get_capitoli_volumi_from_anilist($manga_id);
        if ($response["status"] === "OK") {
            $titolo = $response["dato"]["titolo"];
            $cap_max = $response["dato"]["capitoli"];
            $vol_max = $response["dato"]["volumi"];
        } else {
            $titolo = "Titolo non disponibile";
            $cap_max = 0;
            $vol_max = 0;
        }

        // Altri parametri
        $status = "Planning";
        if (isset($_GET["status"])) {
            $status = trim($_GET["status"]);
            if (!in_array($status, ["Reading", "Complete", "Planning", "Paused", "Dropped"])) {
                $status = "Planning";
            }
        }

        $punteggio = 0;
        if (isset($_GET["punteggio"])) {
            if (preg_match('/^-?\d+(\.\d+)?$/', $_GET["punteggio"])) {
                $punteggio = floatval($_GET["punteggio"]);
                if ($punteggio < 0){
                    $punteggio = 0;
                } 
                if ($punteggio > 10){
                    $punteggio = 10;
                }
            }
        }

        $capitoli_letti = 0;
        if (isset($_GET["capitoli_letti"])) {
            if (preg_match('/^\d+$/', $_GET["capitoli_letti"])) {
                $capitoli_letti = intval($_GET["capitoli_letti"]);
            }
        }

        $volumi_letti = 0;
        if (isset($_GET["volumi_letti"])) {
            if (preg_match('/^\d+$/', $_GET["volumi_letti"])) {
                $volumi_letti = intval($_GET["volumi_letti"]);
            }
        }

        // Date
        $start_date = null;
        $end_date = null;

        if (isset($_GET["start_date"]) && valida_data($_GET["start_date"])) {
            $start_date = $_GET["start_date"];
        }

        if (isset($_GET["end_date"]) && valida_data($_GET["end_date"])) {
            $end_date = $_GET["end_date"];
        }

        // Logica date in base allo status
        if ($status === "Planning") {
            // leave null
        } else if ($status === "Reading") {
            if (!$start_date) {
                $start_date = date("Y-m-d");
            }
        } else if ($status === "Complete") {
            $oggi = date("Y-m-d");
            if (!$start_date) {
                $start_date = $oggi;
            }
            if (!$end_date) {
                $end_date = $oggi;
            }
        }

        // Correzione date invertite
        if ($start_date && $end_date && strtotime($start_date) > strtotime($end_date)) {
            $temp = $start_date;
            $start_date = $end_date;
            $end_date = $temp;
        }

        $note = null;
        if (isset($_GET["note"])) {
            $note = trim($_GET["note"]);
        }

        $preferito = 0;
        if (isset($_GET["preferito"])) {
            if (preg_match('/^\d+$/', $_GET["preferito"])) {
                $preferito = intval($_GET["preferito"]);
            }
        }

        // Controllo se già esiste attività
        $stmt = $conn->prepare("SELECT id, status, punteggio, capitoli_letti, volumi_letti, data_inizio, data_fine, note, preferito
            FROM attivita_manga 
            WHERE utente_id = ? AND riferimento_api = ?");
        $stmt->bind_param("ii", $utente_id, $manga_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $attivita_id = $row["id"];

            // Controllo modifiche
            if ($status !== $row["status"] || $punteggio != $row["punteggio"] || $capitoli_letti != $row["capitoli_letti"] || $volumi_letti != $row["volumi_letti"] || $start_date !== $row["data_inizio"] || $end_date !== $row["data_fine"] || $note !== $row["note"] || $preferito != $row["preferito"]) {
                $stmt_update = $conn->prepare("UPDATE attivita_manga 
                    SET titolo = ?, status = ?, punteggio = ?, capitoli_letti = ?, volumi_letti = ?, data_inizio = ?, data_fine = ?, note = ?, preferito = ?, data_ora = NOW()
                    WHERE id = ?");
                $stmt_update->bind_param("ssdiisssii", $titolo, $status, $punteggio, $capitoli_letti, $volumi_letti, $start_date, $end_date, $note, $preferito, $attivita_id);
                $stmt_update->execute();

                $ret["status"] = "OK";
                $ret["message"] = "Attività aggiornata.";
            }

        } else {
            $stmt_insert = $conn->prepare("INSERT INTO attivita_manga 
            (utente_id, titolo, riferimento_api, status, punteggio, capitoli_letti, volumi_letti, data_inizio, data_fine, note, preferito)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt_insert->bind_param("isssdiiissi", $utente_id, $titolo, $manga_id, $status, $punteggio, $capitoli_letti, $volumi_letti, $start_date, $end_date, $note, $preferito);

            $stmt_insert->execute();

            $ret["status"] = "OK";
            $ret["message"] = "Attività inserita.";
        }
    } else {
        $ret["status"] = "ERROR";
        $ret["message"] = "Parametri mancanti.";
    }

    echo json_encode($ret);
?>
