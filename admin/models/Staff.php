<?php

require_once __DIR__ . '/../../config/database.php';

class Staff {
    private $conn;
    private $table_name = "staff";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    private function hasColumn($columnName) {
        try {
            $q = "SHOW COLUMNS FROM " . $this->table_name . " LIKE :col";
            $stmt = $this->conn->prepare($q);
            $stmt->bindParam(":col", $columnName);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getAll() {
        $hasRole = $this->hasColumn('role');
        $cols = "id, username, email, full_name, created_at";
        if ($hasRole) {
            $cols .= ", role";
        }

        $query = "SELECT " . $cols . " FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $hasRole = $this->hasColumn('role');

        $username = trim($data['username'] ?? '');
        $email = trim($data['email'] ?? '');
        $full_name = trim($data['full_name'] ?? '');
        $password = $data['password'] ?? '';
        $role = trim($data['role'] ?? '');

        $hashed = password_hash($password, PASSWORD_DEFAULT);

        if ($hasRole) {
            $query = "INSERT INTO " . $this->table_name . " (username, email, password, full_name, role)
                      VALUES (:username, :email, :password, :full_name, :role)";
        } else {
            $query = "INSERT INTO " . $this->table_name . " (username, email, password, full_name)
                      VALUES (:username, :email, :password, :full_name)";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":password", $hashed);
        $stmt->bindParam(":full_name", $full_name);
        if ($hasRole) {
            $stmt->bindParam(":role", $role);
        }

        return $stmt->execute();
    }

    public function update($id, $data) {
        $hasRole = $this->hasColumn('role');

        $username = trim($data['username'] ?? '');
        $email = trim($data['email'] ?? '');
        $full_name = trim($data['full_name'] ?? '');
        $role = trim($data['role'] ?? '');
        $newPassword = $data['password'] ?? '';

         
        $setPassword = !empty($newPassword);
        $passwordSql = $setPassword ? ", password = :password" : "";
        $roleSql = $hasRole ? ", role = :role" : "";

        $query = "UPDATE " . $this->table_name . "
                  SET username = :username,
                      email = :email,
                      full_name = :full_name
                      " . $roleSql . "
                      " . $passwordSql . ",
                      updated_at = NOW()
                  WHERE id = :id";

         
        $query = str_replace(" ,", ",", $query);

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":full_name", $full_name);
        if ($hasRole) {
            $stmt->bindParam(":role", $role);
        }
        if ($setPassword) {
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt->bindParam(":password", $hashed);
        }
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
}

?>

