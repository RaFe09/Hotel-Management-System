<?php

require_once __DIR__ . '/../config/database.php';

class Customer {
    private $conn;
    private $table_name = "customers";

    public $id;
    public $first_name;
    public $last_name;
    public $email;
    public $phone;
    public $password;
    public $created_at;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Check if email exists
     */
    public function emailExists() {
        $query = "SELECT id, first_name, last_name, email, phone 
                  FROM " . $this->table_name . " 
                  WHERE email = :email 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->phone = $row['phone'];
            return true;
        }

        return false;
    }

    /**
     * Create customer (for admin booking - allows creating without password)
     */
    public function create($requirePassword = false) {
        $query = "INSERT INTO " . $this->table_name . "
                  SET first_name=:first_name, last_name=:last_name, 
                      email=:email, phone=:phone, password=:password";

        $stmt = $this->conn->prepare($query);

        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));

        // Generate a random password for admin-created customers if not provided
        if (empty($this->password)) {
            $randomPassword = bin2hex(random_bytes(8));
            $this->password = password_hash($randomPassword, PASSWORD_DEFAULT);
        } else {
            $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        }

        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":password", $this->password);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    /**
     * Get customer by email
     */
    public function getByEmail($email) {
        $query = "SELECT id, first_name, last_name, email, phone, created_at 
                  FROM " . $this->table_name . " 
                  WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return null;
    }

    /**
     * Get customer by ID
     */
    public function getById($id) {
        $query = "SELECT id, first_name, last_name, email, phone, created_at 
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

    /**
     * Get all customers (for admin)
     */
    public function getAll() {
        $query = "SELECT id, first_name, last_name, email, phone, created_at 
                  FROM " . $this->table_name . " 
                  ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update customer
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET first_name=:first_name, last_name=:last_name, 
                      email=:email, phone=:phone
                  WHERE id=:id";
        
        $stmt = $this->conn->prepare($query);
        
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":id", $this->id);
        
        return $stmt->execute();
    }

    /**
     * Delete customer
     */
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }
}
?>
