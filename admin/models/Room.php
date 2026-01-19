<?php
/**
 * ROOM MODEL
 * 
 * WHAT IS THIS FILE?
 * This file handles all database operations for rooms.
 * It can add, update, delete, and retrieve room information.
 * 
 * FOR BEGINNERS:
 * - This is like a helper that talks to the database about rooms
 * - Instead of writing database queries everywhere, we put them here
 */

// Include the database connection file
require_once __DIR__ . '/../../config/database.php';

/**
 * Room Class
 * This class handles all room database operations
 */
class Room {
    // Database connection and table name
    private $conn;                      // Database connection
    private $table_name = "rooms";      // Name of the rooms table in database

    // Room properties - these store room information
    public $id;                 // Room ID (automatically assigned)
    public $room_number;        // Room number (e.g., "101", "202")
    public $room_type;          // Type of room (e.g., "Deluxe Room", "Suite")
    public $status;             // Room status: 'available', 'booked', 'maintenance'
    public $floor_number;       // Which floor the room is on
    public $price_per_night;    // Cost per night
    public $description;        // Description of the room
    public $created_at;         // When room was added (automatic)
    public $updated_at;         // When room was last updated (automatic)

    /**
     * CONSTRUCTOR
     * This runs automatically when we create a new Room object
     * It connects to the database
     */
    public function __construct() {
        // Create database connection
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
