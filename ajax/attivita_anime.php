<?php
     //TODO: controllo ep massimi quando si mette complete e passare il titolo con l'endpoint di anilist
    require_once("../classi/db.php");
    session_start();

    $ret = [];

    // Funzione per ottenere anno di uscita e formato da Anilist
    function get_anno_formato_from_anilist($anime_id) {
        $ret = [];

        if (!isset($anime_id) || !preg_match('/^\d+$/', $anime_id)) {
            $ret["status"] = "ERR";
            $ret["msg"] = "ID anime mancante o non valido.";
            $ret["dato"] = null;
            return $ret;
        }

        $query = '
            query ($id: Int) {
                Media(id: $id, type: ANIME) {
                    startDate {
                        year
                    }
                    format
                }
            }
        ';

        $variables = ['id' => intval($anime_id)];

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

        if (isset($data["data"]["Media"]["startDate"]["year"]) && isset($data["data"]["Media"]["format"])) {
            $ret["status"] = "OK";
            $ret["msg"] = "";
            $ret["dato"] = [
                "anno_uscita" => $data["data"]["Media"]["startDate"]["year"],
                "formato" => $data["data"]["Media"]["format"]
            ];
        } else {
            $ret["status"] = "ERR";
            $ret["msg"] = "Informazioni su anno e formato non trovate su Anilist.";
            $ret["dato"] = null;
        }

        return $ret;
    }

    function valida_data($data) {
        $date_format = 'Y-m-d';
        $d = DateTime::createFromFormat($date_format, $data);
        return $d && $d->format($date_format) === $data;
    }

    // --- Inizio codice principale ---

    if (isset($_GET["utente_id"]) && isset($_GET["anime_id"])) {

        $utente_id = $_GET["utente_id"];
        $anime_id = $_GET["anime_id"];

        if (!preg_match('/^\d+$/', $utente_id) || !preg_match('/^\d+$/', $anime_id)) {
            $ret["status"] = "ERROR";
            $ret["message"] = "ID utente o ID anime non valido.";
            echo json_encode($ret);
            die();
        }

        $utente_id = intval($utente_id);
        $anime_id = intval($anime_id);

        // Titolo
        $titolo = "";
        if (isset($_GET["titolo"])) {
            $titolo = trim($_GET["titolo"]);
        }

        // Status
        $status = "Planning";
        if (isset($_GET["status"])) {
            $status = trim($_GET["status"]);
            if (!in_array($status, ["Watching", "Complete", "Planning"])) {
                $status = "Planning";
            }
        }

        // Punteggio
        $punteggio = 0;
        if (isset($_GET["punteggio"])) {
            if (preg_match('/^-?\d+(\.\d+)?$/', $_GET["punteggio"])) {
                $punteggio = floatval($_GET["punteggio"]);
                if ($punteggio < 0) $punteggio = 0;
                if ($punteggio > 10) $punteggio = 10;
            }
        }

        // Episodi visti
        $episodi_visti = 0;
        if (isset($_GET["episodi_visti"])) {
            if (preg_match('/^\d+$/', $_GET["episodi_visti"])) {
                $episodi_visti = intval($_GET["episodi_visti"]);
            }
        }

        // Recuperiamo anno di uscita e formato da Anilist
        $response = get_anno_formato_from_anilist($anime_id);
        $anno_uscita = null;
        $formato = null;
        if ($response["status"] === "OK") {
            $anno_uscita = $response["dato"]["anno_uscita"];
            $formato = $response["dato"]["formato"];
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

        // Logica in base allo status
        if ($status === "Planning") {
            // In Planning, se date non messe, rimangono null
            if (!$start_date) {
                $start_date = null;
            }
            if (!$end_date) {
                $end_date = null;
            }
        } else if ($status === "Watching") {
            // In Watching, se start_date non messo, metti oggi
            if (!$start_date) {
                $start_date = date("Y-m-d");
            }
            // end_date resta null se non messa
        } else if ($status === "Complete") {
            // In Complete, se start_date o end_date non messi, metti oggi
            $oggi = date("Y-m-d");
            if (!$start_date) {
                $start_date = $oggi;
            }
            if (!$end_date) {
                $end_date = $oggi;
            }
        }

        // Controllo coerenza date
        if ($start_date && $end_date) {
            if (strtotime($start_date) > strtotime($end_date)) {
                $temp = $start_date;
                $start_date = $end_date;
                $end_date = $temp;
            }
        }

        // Note
        $note = null;
        if (isset($_GET["note"])) {
            $note = trim($_GET["note"]);
        }

        // Rewatch
        $rewatch = 0;
        if (isset($_GET["rewatch"])) {
            if (preg_match('/^\d+$/', $_GET["rewatch"])) {
                $rewatch = intval($_GET["rewatch"]);
            }
        }

        // Preferito
        $preferito = 0;
        if (isset($_GET["preferito"])) {
            if (preg_match('/^\d+$/', $_GET["preferito"])) {
                $preferito = intval($_GET["preferito"]);
            }
        }

        // Controllo se esiste già attività
        $stmt = $conn->prepare("SELECT id, status, punteggio, episodi_visti, data_inizio, data_fine, note, rewatch, preferito, titolo, anno_uscita, formato 
        FROM attivita_anime 
        WHERE utente_id = ? AND riferimento_api = ?");
        $stmt->bind_param("ii", $utente_id, $anime_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $attivita_id = $row["id"];
        
            if ($status !== $row["status"] || $punteggio != $row["punteggio"] || $episodi_visti != $row["episodi_visti"] || $start_date !== $row["data_inizio"] || $end_date !== $row["data_fine"] || $note !== $row["note"] || $rewatch != $row["rewatch"] || $preferito != $row["preferito"] || $anno_uscita != $row["anno_uscita"] || $formato != $row["formato"]) {
                $stmt_update = $conn->prepare("UPDATE attivita_anime 
                    SET status = ?, punteggio = ?, episodi_visti = ?, data_inizio = ?, data_fine = ?, note = ?, rewatch = ?, preferito = ?, titolo = ?, anno_uscita = ?, formato = ?, data_ora = NOW() 
                    WHERE id = ?");
                $stmt_update->bind_param("sdisssisssii", $status, $punteggio, $episodi_visti, $start_date, $end_date, $note, $rewatch, $preferito, $titolo, $anno_uscita, $formato, $attivita_id);
                $stmt_update->execute();

                $ret["status"] = "OK";
                $ret["message"] = "Attività aggiornata correttamente!";
            }
        } else {
            // Inserisci nuova attività
            $stmt_insert = $conn->prepare("INSERT INTO attivita_anime (utente_id, riferimento_api, status, punteggio, episodi_visti, data_inizio, data_fine, note, rewatch, preferito, titolo, anno_uscita, formato) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt_insert->bind_param("iisdissssisss", $utente_id, $anime_id, $status, $punteggio, $episodi_visti, $start_date, $end_date, $note, $rewatch, $preferito, $titolo, $anno_uscita, $formato);
            $stmt_insert->execute();
            
            $ret["status"] = "OK";
            $ret["message"] = "Attività inserita correttamente!";
        }

        echo json_encode($ret);
    }
?>