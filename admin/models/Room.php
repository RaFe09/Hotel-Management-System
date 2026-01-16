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

    


    public function getByType($roomType) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE room_type = :room_type 
                  ORDER BY floor_number, room_number";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":room_type", $roomType);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    


    public function updateStatus($id, $status) {
        $validStatuses = ['available', 'booked', 'maintenance'];
        if (!in_array($status, $validStatuses)) {
            return false;
        }

        $query = "UPDATE " . $this->table_name . " 
                  SET status = :status, updated_at = NOW() 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    


    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (room_number, room_type, status, floor_number, price_per_night, description)
                  VALUES
                  (:room_number, :room_type, :status, :floor_number, :price_per_night, :description)";

        $stmt = $this->conn->prepare($query);

        $room_number = trim($data['room_number'] ?? '');
        $room_type = trim($data['room_type'] ?? '');
        $status = $data['status'] ?? 'available';
        $floor_number = intval($data['floor_number'] ?? 0);
        $price_per_night = $data['price_per_night'] ?? 0;
        $description = trim($data['description'] ?? '');

        $stmt->bindParam(":room_number", $room_number);
        $stmt->bindParam(":room_type", $room_type);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":floor_number", $floor_number);
        $stmt->bindParam(":price_per_night", $price_per_night);
        $stmt->bindParam(":description", $description);

        return $stmt->execute();
    }

    


    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . "
                  SET room_number = :room_number,
                      room_type = :room_type,
                      status = :status,
                      floor_number = :floor_number,
                      price_per_night = :price_per_night,
                      description = :description,
                      updated_at = NOW()
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $room_number = trim($data['room_number'] ?? '');
        $room_type = trim($data['room_type'] ?? '');
        $status = $data['status'] ?? 'available';
        $floor_number = intval($data['floor_number'] ?? 0);
        $price_per_night = $data['price_per_night'] ?? 0;
        $description = trim($data['description'] ?? '');

        $stmt->bindParam(":room_number", $room_number);
        $stmt->bindParam(":room_type", $room_type);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":floor_number", $floor_number);
        $stmt->bindParam(":price_per_night", $price_per_night);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    


    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    


    public function getDistinctTypes() {
        $query = "SELECT DISTINCT room_type FROM " . $this->table_name . " ORDER BY room_type";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
?>
