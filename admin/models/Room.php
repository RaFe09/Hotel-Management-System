<?php

require_once __DIR__ . '/../config/database.php';

class Room {
    private $conn;
    private $table_name = "rooms";

    public $id;
    public $room_number;
    public $room_type;
    public $status;
    public $floor_number;
    public $price_per_night;
    public $description;
    public $created_at;
    public $updated_at;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Get all rooms
     */
    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY floor_number, room_number";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get rooms by type
     */
    public function getByType($roomType) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE room_type = :room_type 
                  ORDER BY floor_number, room_number";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":room_type", $roomType);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get room statistics
     */
    public function getStatistics() {
        $query = "SELECT 
                    status,
                    COUNT(*) as count
                  FROM " . $this->table_name . "
                  GROUP BY status";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stats = [
            'available' => 0,
            'booked' => 0,
            'maintenance' => 0,
            'total' => 0
        ];

        foreach ($results as $row) {
            $stats[$row['status']] = (int)$row['count'];
            $stats['total'] += (int)$row['count'];
        }

        return $stats;
    }

    /**
     * Get room by ID
     */
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
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
