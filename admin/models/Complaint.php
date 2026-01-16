<?php

require_once __DIR__ . '/../../config/database.php';

class Complaint {
    private $conn;
    private $table_name = "complaints";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function tableExists() {
        try {
            $stmt = $this->conn->prepare("SHOW TABLES LIKE :t");
            $stmt->bindParam(":t", $this->table_name);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getAll() {
        try {
            $query = "SELECT cmp.*, c.first_name, c.last_name, c.email
                      FROM " . $this->table_name . " cmp
                      LEFT JOIN customers c ON cmp.customer_id = c.id
                      ORDER BY cmp.created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function updateReplyAndStatus($id, $status, $reply) {
        try {
            $query = "UPDATE " . $this->table_name . "
                      SET status = :status,
                          admin_reply = :admin_reply,
                          updated_at = NOW()
                      WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":status", $status);
            $stmt->bindParam(":admin_reply", $reply);
            $stmt->bindParam(":id", $id);
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }
}

?>

