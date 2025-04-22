<?php
class Utente {
    private $id;
    private $username;
    private $first_name;
    private $last_name;
    private $phone;
    private $email;
    private $birthdate;
    private $profile_image;
    private $password;
    private $tipo;

    public function __construct($id, $username, $first_name, $last_name, $phone, $email, $birthdate, $profile_image, $password, $tipo) {
        $this->id = $id;
        $this->username = $username;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->phone = $phone;
        $this->email = $email;
        $this->birthdate = $birthdate;
        $this->profile_image = $profile_image;
        $this->password = $password;
        $this->tipo = $tipo;
    }

    // Getter per l'ID
    public function getId() {
        return $this->id;
    }

    // Getter per lo username
    public function getUsername() {
        return $this->username;
    }

    // Getter per il nome
    public function getFirstName() {
        return $this->first_name;
    }

    // Getter per il cognome
    public function getLastName() {
        return $this->last_name;
    }

    // Getter per il numero di telefono
    public function getPhone() {
        return $this->phone;
    }

    // Getter per l'email
    public function getEmail() {
        return $this->email;
    }

    // Getter per la data di nascita
    public function getBirthdate() {
        return $this->birthdate;
    }

    // Getter per l'immagine del profilo
    public function getProfileImage() {
        return $this->profile_image;
    }

    // Getter per il tipo (admin o utente)
    public function getTipo() {
        return $this->tipo;
    }

    // LOGIN con binding param
    public static function login($conn, $username, $password) {
        $password_hash = md5($password);
        $stmt = $conn->prepare("SELECT * FROM utenti WHERE username = ? AND password = ?");
        $stmt->bind_param("ss", $username, $password_hash);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            return new Utente($user['id'], $user['username'], $user['first_name'], $user['last_name'], $user['phone'], $user['email'], $user['birthdate'], $user['profile_image'], $user['password'], $user['tipo']);
        }

        return null;
    }

    // REGISTER con binding param
    public static function register($conn, $username, $first_name, $last_name, $phone, $email, $birthdate, $profile_image, $password, $tipo = 'utente') {
        // Controlla se username o email esistono già
        $stmt_check = $conn->prepare("SELECT id FROM utenti WHERE username = ? OR email = ?");
        $stmt_check->bind_param("ss", $username, $email);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check && $result_check->num_rows > 0) {
            return "Username o email già esistente!";
        }

        // Inserisci nuovo utente
        $password_hash = md5($password);
        $stmt_insert = $conn->prepare("INSERT INTO utenti (username, first_name, last_name, phone, email, birthdate, profile_image, password, tipo)
                                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt_insert->bind_param("sssssssss", $username, $first_name, $last_name, $phone, $email, $birthdate, $profile_image, $password_hash, $tipo);

        if ($stmt_insert->execute()) {
            return "Registrazione avvenuta con successo!";
        } else {
            return "Errore nella registrazione!";
        }
    }
}
?>
