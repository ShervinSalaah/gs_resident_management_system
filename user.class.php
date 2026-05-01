<?php
require_once 'connect.php';

class User {
    private $link;
    private $table = "gs_details";

    public function __construct() {
        global $link;
        $this->link = $link;
        
        // Check if connection is valid
        if ($this->link->connect_error) {
            die("Database connection failed: " . $this->link->connect_error);
        }
    }

    // Register a new user – returns 'success', 'email_exists', or 'error'
    public function register($name, $email, $password) {
        // Check if email already exists
        if ($this->emailExists($email)) {
            return "email_exists";
        }

        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Prepare and execute insert
        $stmt = $this->link->prepare("INSERT INTO " . $this->table . " (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $hashedPassword);
        
        if ($stmt->execute()) {
            $stmt->close();
            return "success";
        } else {
            $stmt->close();
            return "error";
        }
    }

    // Check if email already exists
    private function emailExists($email) {
        $stmt = $this->link->prepare("SELECT id FROM " . $this->table . " WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    // Login – returns user array or false
    public function login($email, $password) {
        $stmt = $this->link->prepare("SELECT id, name, email, password FROM " . $this->table . " WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $stmt->close();
                return $user;
            }
        }
        
        $stmt->close();
        return false;
    }
}
?>