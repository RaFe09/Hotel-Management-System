<?php

require_once __DIR__ . '/../../config/database.php';

class Admin {
    private $conn;
    private $table_name = "admins";

    public $id;
    public $username;
    public $email;
    public $password;
    public $full_name;
    public $created_at;
    public $updated_at;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    


    public function usernameExists() {
        $query = "SELECT id, username, email, password, full_name 
                  FROM " . $this->table_name . " 
                  WHERE username = :username 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $this->username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->email = $row['email'];
            $this->password = $row['password'];
            $this->full_name = $row['full_name'];
            return true;
        }

        return false;
    }

    


    public function emailExists() {
        $query = "SELECT id, username, email, password, full_name 
                  FROM " . $this->table_name . " 
                  WHERE email = :email 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->password = $row['password'];
            $this->full_name = $row['full_name'];
            return true;
        }

        return false;
    }

    


    public function loginByEmail() {
        $plain_password = $this->password;
        
        if ($this->emailExists()) {
             
            $hashed_password = $this->password;
             
            if (password_verify($plain_password, $hashed_password)) {
                return true;
            }
        }
        return false;
    }

    


    public function login() {
        $plain_password = $this->password;
        
        if ($this->usernameExists()) {
             
            $hashed_password = $this->password;
             
            if (password_verify($plain_password, $hashed_password)) {
                return true;
            }
        }
        return false;
    }

    


    public function getById($id) {
        $query = "SELECT id, username, email, full_name, created_at 
                  FROM " . $this->table_name . " 
                  WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return null;
    }
}
?>
