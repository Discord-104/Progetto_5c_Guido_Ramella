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
        $date_format = 'Y-m-d';
        $d = DateTime::createFromFormat($date_format, $data);
        return $d && $d->format($date_format) === $data;
    }

    // Funzione per ottenere titolo, capitoli max, volumi max e formato da Anilist
    function get_info_manga_da_anilist($manga_id) {
        $risultato = [];

        if (!isset($manga_id) || !preg_match('/^\d+$/', $manga_id)) {
            $risultato["status"] = "ERR";
            $risultato["msg"] = "ID manga non valido o mancante.";
            $risultato["dato"] = null;
            return $risultato;
        }

        $query = '
            query ($id: Int) {
                Media(id: $id, type: MANGA) {
                    title {
                        romaji
                    }
                    chapters
                    volumes
                    startDate {
                        year
                    }
                    format
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
            $risultato["status"] = "ERR";
            $risultato["msg"] = "Errore nella connessione ad Anilist.";
            $risultato["dato"] = null;
            return $risultato;
        }

        $data = json_decode($result, true);

        if (isset($data["data"]["Media"])) {
            $manga = $data["data"]["Media"];
            
            // Usa solo il titolo romaji
            $titolo = "Titolo non disponibile";
            if (!empty($manga["title"]["romaji"])) {
                $titolo = $manga["title"]["romaji"];
            }

            $risultato["status"] = "OK";
            $risultato["msg"] = "";
            $anno_uscita = null;
            if (isset($manga["startDate"]["year"])) {
                $anno_uscita = $manga["startDate"]["year"];
            }
            
            $formato = null;
            if (isset($manga["format"])) {
                $formato = $manga["format"];
            }
            
            $risultato["dato"] = [
                "titolo" => $titolo,
                "capitoli_max" => $manga["chapters"], // Può essere null
                "volumi_max" => $manga["volumes"],    // Può essere null
                "anno_uscita" => $anno_uscita,
                "formato" => $formato
            ];
        } else {
            $risultato["status"] = "ERR";
            $risultato["msg"] = "Dati non trovati su Anilist.";
            $risultato["dato"] = null;
        }

        return $risultato;
    }

    // --- Inizio codice principale ---

    if (isset($_GET["manga_id"])) {

        $manga_id = $_GET["manga_id"];

        if (!preg_match('/^\d+$/', $manga_id)) {
            $ret["status"] = "ERROR";
            $ret["message"] = "ID manga non valido.";
            echo json_encode($ret);
            die();
        }

        $utente_id = intval($utente_id);
        $manga_id = intval($manga_id);

        // Ottieni informazioni dal manga da Anilist
        $info_manga = get_info_manga_da_anilist($manga_id);
        
        $titolo = "Titolo non disponibile";
        $capitoli_max = null;
        $volumi_max = null;
        $anno_uscita = null;
        $formato = null;
        
        if ($info_manga["status"] === "OK") {
            $dato = $info_manga["dato"];
            if (!empty($dato["titolo"])) {
                $titolo = $dato["titolo"];
            }
            $capitoli_max = $dato["capitoli_max"];
            $volumi_max = $dato["volumi_max"]; 
            $anno_uscita = $dato["anno_uscita"];
            $formato = $dato["formato"];
        }
        
        // Se il titolo è stato passato manualmente, lo utilizziamo solo se non abbiamo recuperato niente da Anilist
        if ($titolo == "Titolo non disponibile" && isset($_GET["titolo"]) && !empty($_GET["titolo"])) {
            $titolo = trim($_GET["titolo"]);
        }

        // Status
        $status = "Planning";
        if (isset($_GET["status"])) {
            $status = trim($_GET["status"]);
            if (!in_array($status, ["Reading", "Complete", "Planning", "Paused", "Dropped"])) {
                $status = "Planning";
            }
        }

        // Punteggio
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

        // Capitoli letti con controllo sul massimo
        $capitoli_letti = 0;
        if (isset($_GET["capitoli_letti"])) {
            if (preg_match('/^-?\d+$/', $_GET["capitoli_letti"])) {
                $capitoli_letti = intval($_GET["capitoli_letti"]);

                // Se negativo, imposta a 0
                if ($capitoli_letti < 0) {
                    $capitoli_letti = 0;
                }
            }
        } else {
            // Se lo status è "Complete", imposta i capitoli letti al massimo
            if ($status === "Complete" && $capitoli_max !== null) {
                $capitoli_letti = $capitoli_max;
            }
        }

        // Volumi letti con controllo sul massimo
        $volumi_letti = 0;
        if (isset($_GET["volumi_letti"])) {
            if (preg_match('/^-?\d+$/', $_GET["volumi_letti"])) {
                $volumi_letti = intval($_GET["volumi_letti"]);

                // Se negativo, imposta a 0
                if ($volumi_letti < 0) {
                    $volumi_letti = 0;
                }
            }
        } else {
            // Se lo status è "Complete", imposta i volumi letti al massimo
            if ($status === "Complete" && $volumi_max !== null) {
                $volumi_letti = $volumi_max;
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

        // Logica in base allo status
        if ($status === "Planning") {
            if (!$start_date) {
                $start_date = null;
            }
            if (!$end_date) {
                $end_date = null;
            }
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

        // Rereading con controllo per valori negativi
        $rereading = 0;
        if (isset($_GET["reread"])) {
            if (preg_match('/^-?\d+$/', $_GET["reread"])) {
                $rereading = intval($_GET["reread"]);
                // Se negativo, imposta a 0
                if ($rereading < 0) {
                    $rereading = 0;
                }
            }
        }

        // Preferito - assicurati che sia 0 o 1
        $preferito = 0;
        if (isset($_GET["preferito"])) {
            if (intval($_GET["preferito"]) == 1) {
                $preferito = 1;
            } else {
                $preferito = 0;
            }
        }

        // Controllo se esiste già attività
        $stmt = $conn->prepare("SELECT id FROM attivita_manga 
                               WHERE utente_id = ? AND riferimento_api = ?");
        $stmt->bind_param("ii", $utente_id, $manga_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $attivita_id = $row["id"];
            
            $stmt_update = $conn->prepare("UPDATE attivita_manga 
                SET titolo = ?, status = ?, punteggio = ?, capitoli_letti = ?, volumi_letti = ?, 
                    data_inizio = ?, data_fine = ?, note = ?, rereading = ?, preferito = ?, anno = ?, formato = ?, data_ora = NOW() 
                WHERE id = ?");
            $stmt_update->bind_param("ssdiisssisisi", $titolo, $status, $punteggio, $capitoli_letti, $volumi_letti, 
                                   $start_date, $end_date, $note, $rereading, $preferito, $anno_uscita, $formato, $attivita_id);
            $stmt_update->execute();

            $ret["status"] = "OK";
            $ret["message"] = "Attività aggiornata correttamente!";
        } else {
            $stmt_insert = $conn->prepare("INSERT INTO attivita_manga 
                (utente_id, riferimento_api, titolo, status, punteggio, capitoli_letti, volumi_letti, 
                 data_inizio, data_fine, note, rereading, preferito, anno, formato, data_ora) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt_insert->bind_param("iissdiisssiiss", $utente_id, $manga_id, $titolo, $status, $punteggio, 
                                    $capitoli_letti, $volumi_letti, $start_date, $end_date, $note, 
                                    $rereading, $preferito, $anno_uscita, $formato);
            $stmt_insert->execute();

            $ret["status"] = "OK";
            $ret["message"] = "Attività aggiunta correttamente!";
            
            $ret["info"] = [
                "titolo_trovato" => $titolo != "Titolo non disponibile",
                "capitoli_max" => $capitoli_max,
                "volumi_max" => $volumi_max,
                "anno_uscita" => $anno_uscita,
                "formato" => $formato
            ];
        }

        echo json_encode($ret);
    } else {
        $ret["status"] = "ERROR";
        $ret["message"] = "ID manga non forniti.";
        echo json_encode($ret);
    }
?>