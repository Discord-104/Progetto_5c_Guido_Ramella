<?php
    require_once("../classi/db.php");
    session_start();

    $ret = [];

    // Controlla se l'utente è loggato
    if (!isset($_SESSION["utente_id"])) {
        $ret["status"] = "ERROR";
        $ret["message"] = "Utente non autenticato.";
        echo json_encode($ret);
        die();
    }

    // Usa l'ID utente dalla sessione
    $utente_id = $_SESSION["utente_id"];

    function valida_data($data) {
        $d = DateTime::createFromFormat("Y-m-d", $data);
        return $d && $d->format("Y-m-d") === $data;
    }

    if (isset($_GET["videogioco_guid"])) {
        $guid = $_GET["videogioco_guid"];

        // Validazioni iniziali
        if (!preg_match('/^[a-zA-Z0-9-]+$/', $guid)) {
            $ret["status"] = "ERROR";
            $ret["message"] = "GUID non valido.";
            echo json_encode($ret);
            exit;
        }

        $utente_id = intval($utente_id);

        // Chiamata API GiantBomb
        $apiKey = "bb709d6b2114c61e3f0c9999834b43918d1a2427";
        $url = "https://www.giantbomb.com/api/game/$guid/?api_key=$apiKey&format=json&field_list=name,original_release_date";

        $options = [
            "http" => [
                "method" => "GET",
                "header" => "User-Agent: GiantBomb PHP Script\r\n"
            ]
        ];
        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);

        if ($response === false) {
            $ret["status"] = "ERROR";
            $ret["message"] = "Errore durante la chiamata all'API di GiantBomb.";
            echo json_encode($ret);
            exit;
        }

        $data = json_decode($response, true);

        if (!isset($data["results"])) {
            $ret["status"] = "ERROR";
            $ret["message"] = "Dati non validi dall'API di GiantBomb.";
            echo json_encode($ret);
            exit;
        }

        $titolo = "Titolo non disponibile";

        if(isset($data["results"]["name"])) {
            $titolo = $data["results"]["name"];
        } 

        $data_uscita = "?";
        if (isset($data["results"]["original_release_date"]) && valida_data(substr($data["results"]["original_release_date"], 0, 10))) {
            $data_uscita = substr($data["results"]["original_release_date"], 0, 10);
        }

        // STATUS
        $status = "Planning";
        if (isset($_GET["status"])) {
            $tmp = trim($_GET["status"]);
            if (in_array($tmp, ["Playing", "Complete", "Planning", "Paused", "Dropped"])) {
                $status = $tmp;
            }
        }

        // PUNTEGGIO
        $punteggio = 0;
        if (isset($_GET["punteggio"])) {
            if (preg_match('/^-?\d+(\.\d+)?$/', $_GET["punteggio"])) {
                $punteggio = floatval($_GET["punteggio"]);
                if ($punteggio < 0) {
                    $punteggio = 0;
                }
                if ($punteggio > 10) {
                    $punteggio = 10;
                }
            }
        }

        // ORE GIOCATE
        $ore_giocate = 0;
        if (isset($_GET["ore_giocate"])) {
            if (preg_match('/^-?\d+$/', $_GET["ore_giocate"])) {
                $ore_giocate = intval($_GET["ore_giocate"]);
                if ($ore_giocate < 0) {
                    $ore_giocate = 0;
                }
            }
        }

        // DATE
        $start_date = null;
        if (isset($_GET["start_date"]) && valida_data($_GET["start_date"])) {
            $start_date = $_GET["start_date"];
        }

        $end_date = null;
        if (isset($_GET["end_date"]) && valida_data($_GET["end_date"])) {
            $end_date = $_GET["end_date"];
        }

        if ($status === "Playing" && !$start_date) {
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

        // NOTE
        $note = null;
        if (isset($_GET["note"])) {
            $note = trim($_GET["note"]);
        }

        // RIGIOCATO
        $rigiocato = 0;
        if (isset($_GET["rigiocato"])) {
            if (preg_match('/^-?\d+$/', $_GET["rigiocato"])) {
                $rigiocato = intval($_GET["rigiocato"]);
                if ($rigiocato < 0) {
                    $rigiocato = 0;
                }
            }
        }

        // PREFERITO
        $preferito = 0;
        if (isset($_GET["preferito"])) {
            $val_preferito = $_GET["preferito"];
            if ($val_preferito == 1) {
                $preferito = 1;
            } else {
                $preferito = 0;
            }
        }

        // Controllo attività esistente
        $stmt = $conn->prepare("SELECT id FROM attivita_videogioco WHERE utente_id = ? AND guid = ?");
        $stmt->bind_param("is", $utente_id, $guid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $id = $row["id"];
            $stmt_update = $conn->prepare("UPDATE attivita_videogioco SET titolo = ?, data_uscita = ?, status = ?, punteggio = ?, ore_giocate = ?, start_date = ?, end_date = ?, note = ?, rigiocato = ?, preferito = ?, data_ora = NOW() WHERE id = ?");
            $stmt_update->bind_param("sssdisssiii", $titolo, $data_uscita, $status, $punteggio, $ore_giocate, $start_date, $end_date, $note, $rigiocato, $preferito, $id);
            $stmt_update->execute();

            $ret["status"] = "OK";
            $ret["message"] = "Attività aggiornata con successo.";
        } else {
            $stmt_insert = $conn->prepare("INSERT INTO attivita_videogioco (utente_id, guid, titolo, data_uscita, status, punteggio, ore_giocate, start_date, end_date, note, rigiocato, preferito, data_ora) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt_insert->bind_param("issssdisssii", $utente_id, $guid, $titolo, $data_uscita, $status, $punteggio, $ore_giocate, $start_date, $end_date, $note, $rigiocato, $preferito);
            $stmt_insert->execute();

            $ret["status"] = "OK";
            $ret["message"] = "Attività inserita con successo.";
        }

        $stmt->close();
    } else {
        $ret["status"] = "ERROR";
        $ret["message"] = "Parametri richiesti mancanti.";
    }

    echo json_encode($ret);
?>
