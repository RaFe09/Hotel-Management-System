<?php

require_once __DIR__ . '/../config/database.php';

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

    /**
     * Create a new booking
     */
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

    /**
     * Get available rooms for a date range
     */
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

    /**
     * Update room status to booked
     */
    public function updateRoomStatus($roomId, $status = 'booked') {
        $query = "UPDATE rooms SET status = :status WHERE id = :room_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":room_id", $roomId);
        return $stmt->execute();
    }

    /**
     * Calculate total price
     */
    public function calculateTotalPrice($pricePerNight, $checkIn, $checkOut) {
        $checkInDate = new DateTime($checkIn);
        $checkOutDate = new DateTime($checkOut);
        $nights = $checkInDate->diff($checkOutDate)->days;
        return $pricePerNight * $nights;
    }

    /**
     * Get all bookings (for admin)
     */
    public function getAll() {
        $query = "SELECT b.*, c.first_name, c.last_name, c.email, c.phone, r.room_number
                  FROM " . $this->table_name . " b
                  LEFT JOIN customers c ON b.customer_id = c.id
                  LEFT JOIN rooms r ON b.room_id = r.id
                  ORDER BY b.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get booking by ID
     */
    public function getById($id) {
        $query = "SELECT b.*, c.first_name, c.last_name, c.email, c.phone, r.room_number
                  FROM " . $this->table_name . " b
                  LEFT JOIN customers c ON b.customer_id = c.id
                  LEFT JOIN rooms r ON b.room_id = r.id
                  WHERE b.id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return null;
    }

    /**
     * Update booking status
     */
    public function updateStatus() {
        // Get current booking info to check room_id and old status
        $currentBooking = $this->getById($this->id);
        if (!$currentBooking) {
            return false;
        }
        
        $oldStatus = $currentBooking['status'];
        $roomId = $currentBooking['room_id'];
        
        // Update booking status
        $query = "UPDATE " . $this->table_name . "
                  SET status=:status, updated_at=NOW()
                  WHERE id=:id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id);
        
        if (!$stmt->execute()) {
            return false;
        }
        
        // Update room status based on booking status change
        if ($roomId) {
            // If status changed to cancelled or completed, set room to available
            if ($this->status === 'cancelled' || $this->status === 'completed') {
                $this->updateRoomStatus($roomId, 'available');
            }
            // If status changed to confirmed, set room to booked
            elseif ($this->status === 'confirmed') {
                $this->updateRoomStatus($roomId, 'booked');
            }
            // If status changed to pending from confirmed/cancelled, keep room status as is
            // (pending bookings don't automatically book the room)
        }
        
        return true;
    }

    /**
     * Update booking
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET room_type=:room_type, check_in_date=:check_in_date, 
                      check_out_date=:check_out_date, number_of_guests=:number_of_guests,
                      total_price=:total_price, status=:status, special_requests=:special_requests,
                      updated_at=NOW()
                  WHERE id=:id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":room_type", $this->room_type);
        $stmt->bindParam(":check_in_date", $this->check_in_date);
        $stmt->bindParam(":check_out_date", $this->check_out_date);
        $stmt->bindParam(":number_of_guests", $this->number_of_guests);
        $stmt->bindParam(":total_price", $this->total_price);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":special_requests", $this->special_requests);
        $stmt->bindParam(":id", $this->id);
        
        return $stmt->execute();
    }

    /**
     * Delete booking
     */
    public function delete() {
        // Get booking info before deleting to update room status
        $booking = $this->getById($this->id);
        
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        
        if ($stmt->execute()) {
            // Update room status back to available if booking was confirmed
            if ($booking && $booking['status'] === 'confirmed' && isset($booking['room_id'])) {
                $this->updateRoomStatus($booking['room_id'], 'available');
            }
            return true;
        }
        return false;
    }
}
?>
