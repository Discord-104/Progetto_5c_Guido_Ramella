<?php
    require_once("../classi/db.php");
    session_start();

    $ret = [];

    function valida_data($data) {
        $d = DateTime::createFromFormat("Y-m-d", $data);
        return $d && $d->format("Y-m-d") === $data;
    }

    function get_info_fumetto_comicvine($fumetto_id) {
        $risultato = [];

        if (!isset($fumetto_id) || !preg_match('/^\d+$/', $fumetto_id)) {
            $risultato["status"] = "ERR";
            $risultato["msg"] = "ID fumetto non valido.";
            $risultato["dato"] = null;
            return $risultato;
        }

        $api_key = "22c2e6718a7614c00a5fd89e2a6d8a4cfe8274ce";
        $url = "https://comicvine.gamespot.com/api/issue/4000-" . $fumetto_id . "/?api_key=" . $api_key . "&format=json";

        $options = array(
            "http" => array(
                "header" => "User-Agent: ComicVine PHP Client\r\n"
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result === false) {
            $risultato["status"] = "ERR";
            $risultato["msg"] = "Errore nella richiesta a ComicVine.";
            $risultato["dato"] = null;
            return $risultato;
        }

        $data = json_decode($result, true);

        if (isset($data["results"])) {
            $fumetto = $data["results"];
            $titolo = null;
            $volume = null;
            $anno = null;
            $numero_fumetto = null; // Nuovo campo per il numero del fumetto

            if (isset($fumetto["name"])) {
                $titolo = $fumetto["name"];
            }

            if (isset($fumetto["volume"]["name"])) {
                $volume = $fumetto["volume"]["name"];
            }

            if (isset($fumetto["cover_date"]) && valida_data($fumetto["cover_date"])) {
                $anno = $fumetto["cover_date"];
            }

            // Controlla se il numero del fumetto è disponibile
            if (isset($fumetto["issue_number"])) {
                $numero_fumetto = $fumetto["issue_number"];
            }

            $risultato["status"] = "OK";
            $risultato["msg"] = "";
            $risultato["dato"] = [
                "titolo" => $titolo,
                "volume" => $volume,
                "anno_uscita" => $anno,
                "numero_fumetto" => $numero_fumetto  // Aggiungi il numero del fumetto
            ];
        } else {
            $risultato["status"] = "ERR";
            $risultato["msg"] = "Dati non trovati.";
            $risultato["dato"] = null;
        }

        return $risultato;
    }

    if (isset($_GET["utente_id"]) && isset($_GET["fumetto_id"])) {
        $utente_id = $_GET["utente_id"];
        $fumetto_id = $_GET["fumetto_id"];

        if (!preg_match('/^\d+$/', $utente_id) || !preg_match('/^\d+$/', $fumetto_id)) {
            $ret["status"] = "ERROR";
            $ret["message"] = "ID utente o fumetto non valido.";
            echo json_encode($ret);
            die();
        }

        $utente_id = intval($utente_id);
        $fumetto_id = intval($fumetto_id);

        $info = get_info_fumetto_comicvine($fumetto_id);

        $titolo = null;
        $volume = null;
        $anno_uscita = null;
        $numero_fumetto = null;  // Aggiungi la variabile per il numero del fumetto

        if ($info["status"] === "OK") {
            $dato = $info["dato"];

            if (isset($dato["titolo"])) {
                $titolo = $dato["titolo"];
            }

            if (isset($dato["volume"])) {
                $volume = $dato["volume"];
            }

            if (isset($dato["anno_uscita"])) {
                $anno_uscita = $dato["anno_uscita"];
            }

            // Aggiungi il numero del fumetto, se disponibile
            if (isset($dato["numero_fumetto"])) {
                $numero_fumetto = $dato["numero_fumetto"];
            }
        }

        $status = "Planning";
        if (isset($_GET["status"])) {
            $tmp = trim($_GET["status"]);
            if ($tmp === "Reading" || $tmp === "Complete" || $tmp === "Planning" || $tmp === "Paused" || $tmp === "Dropped") {
                $status = $tmp;
            }
        }

        $punteggio = 0.0;
        if (isset($_GET["punteggio"])) {
            if (is_numeric($_GET["punteggio"])) {
                $punteggio = floatval($_GET["punteggio"]);
                if ($punteggio < 0) {
                    $punteggio = 0.0;
                } else {
                    if ($punteggio > 10) {
                        $punteggio = 10.0;
                    }
                }
            }
        }

        $numero_letti = 0;
        if (isset($_GET["pagine_lette"])) {
            if (preg_match('/^\d+$/', $_GET["pagine_lette"])) {
                $numero_letti = intval($_GET["pagine_lette"]);
            }
        }

        $start_date = null;
        if (isset($_GET["start_date"])) {
            if (valida_data($_GET["start_date"])) {
                $start_date = $_GET["start_date"];
            }
        }

        $end_date = null;
        if (isset($_GET["end_date"])) {
            if (valida_data($_GET["end_date"])) {
                $end_date = $_GET["end_date"];
            }
        }

        if ($status === "Reading" && !$start_date) {
            $start_date = date("Y-m-d");
        }

        if ($status === "Complete") {
            $oggi = date("Y-m-d");
            if (!$start_date) {
                $start_date = $oggi;
            }
            if (!$end_date) {
                $end_date = $oggi;
            }
        }

        if ($start_date && $end_date && strtotime($start_date) > strtotime($end_date)) {
            $tmp = $start_date;
            $start_date = $end_date;
            $end_date = $tmp;
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

        $stmt = $conn->prepare("SELECT id FROM attivita_fumetto WHERE utente_id = ? AND riferimento_api = ?");
        $stmt->bind_param("ii", $utente_id, $fumetto_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $id = $row["id"];
            $stmt_update = $conn->prepare("UPDATE attivita_fumetto SET titolo = ?, status = ?, punteggio = ?, numero_letti = ?, data_inizio = ?, data_fine = ?, note = ?, preferito = ?, nome_volume = ?, anno_uscita = ?, numero_fumetto = ?, data_ora = NOW() WHERE id = ?");
            $stmt_update->bind_param("ssdisssssssi", $titolo, $status, $punteggio, $numero_letti, $start_date, $end_date, $note, $preferito, $volume, $anno_uscita, $numero_fumetto, $id);
            $stmt_update->execute();

            $ret["status"] = "OK";
            $ret["message"] = "Attività aggiornata con successo.";
        } else {
            $stmt_insert = $conn->prepare("INSERT INTO attivita_fumetto (utente_id, titolo, riferimento_api, status, punteggio, numero_letti, data_inizio, data_fine, note, preferito, nome_volume, anno_uscita, numero_fumetto, data_ora) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt_insert->bind_param("isssdiissssss", $utente_id, $titolo, $fumetto_id, $status, $punteggio, $numero_letti, $start_date, $end_date, $note, $preferito, $volume, $anno_uscita, $numero_fumetto);
            $stmt_insert->execute();

            $ret["status"] = "OK";
            $ret["message"] = "Attività inserita con successo.";
        }
    } else {
        $ret["status"] = "ERROR";
        $ret["message"] = "Parametri richiesti non ricevuti.";
    }

    echo json_encode($ret);
?>
