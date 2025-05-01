<?php
require_once("../classi/db.php");
session_start();

$ret = [];

function valida_data($data) {
    $d = DateTime::createFromFormat("Y-m-d", $data);
    return $d && $d->format("Y-m-d") === $data;
}

function get_info_fumetto_comicvine($fumetto_id) {
    $ret = [];

    if (!isset($fumetto_id) || !preg_match('/^\d+$/', $fumetto_id)) {
        $ret["status"] = "ERR";
        $ret["msg"] = "ID fumetto mancante o non valido.";
        $ret["dato"] = null;
        return $ret;
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
        $ret["status"] = "ERR";
        $ret["msg"] = "Errore nella connessione a ComicVine.";
        $ret["dato"] = null;
        return $ret;
    }

    $data = json_decode($result, true);

    if (isset($data["results"])) {
        $fumetto = $data["results"];
        $ret["status"] = "OK";
        $ret["msg"] = "";
        $ret["dato"] = [
            "titolo" => $fumetto["name"],
            "numero" => $fumetto["issue_number"],
            "data_pubblicazione" => $fumetto["cover_date"]
        ];
    } else {
        $ret["status"] = "ERR";
        $ret["msg"] = "Informazioni non trovate su ComicVine.";
        $ret["dato"] = null;
    }

    return $ret;
}

if (isset($_GET["utente_id"]) && isset($_GET["fumetto_id"])) {
    $utente_id = $_GET["utente_id"];
    $fumetto_id = $_GET["fumetto_id"];

    if (!preg_match('/^\d+$/', $utente_id) || !preg_match('/^\d+$/', $fumetto_id)) {
        $ret["status"] = "ERROR";
        $ret["message"] = "ID utente o ID fumetto non valido.";
        echo json_encode($ret);
        die();
    }

    $utente_id = intval($utente_id);
    $fumetto_id = intval($fumetto_id);

    $response = get_info_fumetto_comicvine($fumetto_id);
    if ($response["status"] === "OK") {
        $titolo = $response["dato"]["titolo"];
        $numero = $response["dato"]["numero"];
        $data_pubblicazione = $response["dato"]["data_pubblicazione"];
    } else {
        $titolo = "Titolo non disponibile";
        $numero = 0;
        $data_pubblicazione = null;
    }

    $status = "Planning";
    if (isset($_GET["status"])) {
        $status_tmp = trim($_GET["status"]);
        if ($status_tmp === "Reading" || $status_tmp === "Complete" || $status_tmp === "Planning" || $status_tmp === "Paused" || $status_tmp === "Dropped") {
            $status = $status_tmp;
        }
    }

    $punteggio = 0.0;
    if (isset($_GET["punteggio"])) {
        if (is_numeric($_GET["punteggio"])) {
            $punteggio = floatval($_GET["punteggio"]);
            if ($punteggio < 0) {
                $punteggio = 0;
            } else {
                if ($punteggio > 10) {
                    $punteggio = 10;
                }
            }
        }
    }

    $numero_letti = 0;
    if (isset($_GET["numero_letti"])) {
        if (preg_match('/^\d+$/', $_GET["numero_letti"])) {
            $numero_letti = intval($_GET["numero_letti"]);
            if ($numero && $numero_letti > $numero) {
                $numero_letti = $numero;
            }
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
    } else {
        if ($status === "Complete") {
            $oggi = date("Y-m-d");
            if (!$start_date) {
                $start_date = $oggi;
            }
            if (!$end_date) {
                $end_date = $oggi;
            }
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

    $stmt = $conn->prepare("SELECT id, status, punteggio, numero_letti, data_inizio, data_fine, note, preferito 
        FROM attivita_fumetto 
        WHERE utente_id = ? AND riferimento_api = ?");
    $stmt->bind_param("ii", $utente_id, $fumetto_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $attivita_id = $row["id"];

        if ($status !== $row["status"] || $punteggio != $row["punteggio"] || $numero_letti != $row["numero_letti"] || $start_date !== $row["data_inizio"] || $end_date !== $row["data_fine"] || $note !== $row["note"] || $preferito != $row["preferito"]) {
            $stmt_update = $conn->prepare("UPDATE attivita_fumetto 
                SET titolo = ?, status = ?, punteggio = ?, numero_letti = ?, data_inizio = ?, data_fine = ?, note = ?, preferito = ?, data_ora = NOW()
                WHERE id = ?");
            $stmt_update->bind_param("ssdisssii", $titolo, $status, $punteggio, $numero_letti, $start_date, $end_date, $note, $preferito, $attivita_id);
            $stmt_update->execute();

            $ret["status"] = "OK";
            $ret["message"] = "Attività aggiornata.";
        } else {
            $ret["status"] = "OK";
            $ret["message"] = "Nessuna modifica necessaria.";
        }
    } else {
        $stmt_insert = $conn->prepare("INSERT INTO attivita_fumetto 
            (utente_id, titolo, riferimento_api, status, punteggio, numero_letti, data_inizio, data_fine, note, preferito, data_ora)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt_insert->bind_param("isssdiissi", $utente_id, $titolo, $fumetto_id, $status, $punteggio, $numero_letti, $start_date, $end_date, $note, $preferito);
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
