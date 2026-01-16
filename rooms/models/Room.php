<?php

require_once __DIR__ . '/../../config/database.php';

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

    


    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY floor_number, room_number";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    


    public function getByStatus($status) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE status = :status 
                  ORDER BY floor_number, room_number";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    


    public function getAvailable() {
        return $this->getByStatus('available');
    }

    


    public function getBooked() {
        return $this->getByStatus('booked');
    }

    


    public function getMaintenance() {
        return $this->getByStatus('maintenance');
    }

    


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

    


    public function getByType($roomType) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE room_type = :room_type 
                  ORDER BY floor_number, room_number";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":room_type", $roomType);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    


    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->room_number = $row['room_number'];
            $this->room_type = $row['room_type'];
            $this->status = $row['status'];
            $this->floor_number = $row['floor_number'];
            $this->price_per_night = $row['price_per_night'];
            $this->description = $row['description'];
            return true;
        }
        return false;
    }
}
?>

