<?php

require_once __DIR__ . '/../../config/database.php';

class Booking {
    private $conn;
    private $table_name = "bookings";

    public $id;
    public $customer_id;
    public $room_id;
    public $room_type;
    public $check_in_date;
    public $check_out_date;
    public $number_of_guests;
    public $total_price;
    public $status;
    public $special_requests;
    public $created_at;
    public $updated_at;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    


    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (customer_id, room_id, room_type, check_in_date, check_out_date, 
                   number_of_guests, total_price, status, special_requests)
                  VALUES 
                  (:customer_id, :room_id, :room_type, :check_in_date, :check_out_date,
                   :number_of_guests, :total_price, :status, :special_requests)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":customer_id", $this->customer_id);
        $stmt->bindParam(":room_id", $this->room_id);
        $stmt->bindParam(":room_type", $this->room_type);
        $stmt->bindParam(":check_in_date", $this->check_in_date);
        $stmt->bindParam(":check_out_date", $this->check_out_date);
        $stmt->bindParam(":number_of_guests", $this->number_of_guests);
        $stmt->bindParam(":total_price", $this->total_price);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":special_requests", $this->special_requests);
        
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    


    public function getAvailableRoomsForDates($roomType, $checkIn, $checkOut) {
        $query = "SELECT r.* FROM rooms r
                  WHERE r.room_type = :room_type 
                  AND r.status = 'available'
                  AND r.id NOT IN (
                      SELECT b.room_id FROM bookings b
                      WHERE b.status IN ('pending', 'confirmed')
                      AND (
                          (b.check_in_date <= :check_out AND b.check_out_date >= :check_in)
                      )
                  )
                  ORDER BY r.floor_number, r.room_number
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":room_type", $roomType);
        $stmt->bindParam(":check_in", $checkIn);
        $stmt->bindParam(":check_out", $checkOut);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    


    public function getAvailableRoomsListForDates($roomType, $checkIn, $checkOut) {
        $query = "SELECT r.* FROM rooms r
                  WHERE r.room_type = :room_type
                  AND r.status = 'available'
                  AND r.id NOT IN (
                      SELECT b.room_id FROM bookings b
                      WHERE b.status IN ('pending', 'confirmed')
                      AND (
                          (b.check_in_date <= :check_out AND b.check_out_date >= :check_in)
                      )
                  )
                  ORDER BY r.floor_number, r.room_number";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":room_type", $roomType);
        $stmt->bindParam(":check_in", $checkIn);
        $stmt->bindParam(":check_out", $checkOut);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    



    public function getAvailableRoomByIdForDates($roomId, $roomType, $checkIn, $checkOut) {
        $query = "SELECT r.* FROM rooms r
                  WHERE r.id = :room_id
                  AND r.room_type = :room_type
                  AND r.status = 'available'
                  AND r.id NOT IN (
                      SELECT b.room_id FROM bookings b
                      WHERE b.status IN ('pending', 'confirmed')
                      AND (
                          (b.check_in_date <= :check_out AND b.check_out_date >= :check_in)
                      )
                  )
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":room_id", $roomId);
        $stmt->bindParam(":room_type", $roomType);
        $stmt->bindParam(":check_in", $checkIn);
        $stmt->bindParam(":check_out", $checkOut);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    


    public function updateRoomStatus($roomId, $status = 'booked') {
        $query = "UPDATE rooms SET status = :status WHERE id = :room_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":room_id", $roomId);
        return $stmt->execute();
    }

    


    public function calculateTotalPrice($pricePerNight, $checkIn, $checkOut) {
        $checkInDate = new DateTime($checkIn);
        $checkOutDate = new DateTime($checkOut);
        $nights = $checkInDate->diff($checkOutDate)->days;
        return $pricePerNight * $nights;
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

    


    public function getByCustomerId($customerId) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE customer_id = :customer_id 
                  ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":customer_id", $customerId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

