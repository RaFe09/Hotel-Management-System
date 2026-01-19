<?php
/**
 * BOOKING MODEL
 * This class handles all database operations for bookings
 * 
 * For beginners: A model is like a helper that talks to the database
 * Instead of writing database code everywhere, we put it here
 */

require_once __DIR__ . '/../../config/database.php';

class Booking {
    // Database connection and table name
    private $conn;                      // Database connection object
    private $table_name = "bookings";   // Name of the table in database

    // Booking properties - these store booking information
    public $id;                 // Booking ID (automatically assigned)
    public $customer_id;        // Which customer made the booking
    public $room_id;            // Which room is booked
    public $room_type;          // Type of room (Deluxe, Suite, etc.)
    public $check_in_date;      // When customer checks in
    public $check_out_date;     // When customer checks out
    public $number_of_guests;   // How many guests
    public $total_price;        // Total cost
    public $status;             // Booking status (pending, confirmed, cancelled, completed)
    public $special_requests;   // Any special requests from customer
    public $created_at;         // When booking was created (automatic)
    public $updated_at;         // When booking was last updated (automatic)

    /**
     * Constructor - runs automatically when we create a new Booking object
     * This connects to the database
     */
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }    /**
     * CREATE A NEW BOOKING
     * This function saves a new booking to the database
     * 
     * How it works:
     * 1. Create SQL query to insert booking
     * 2. Use prepared statements (safe from SQL injection)
     * 3. Bind values to query
     * 4. Execute query
     * 5. Get the ID of the newly created booking
     */
    public function create() {
        // STEP 1: Write the SQL query
        // :customer_id, :room_id etc. are placeholders (like blanks to fill in)
        $query = "INSERT INTO " . $this->table_name . " 
                  (customer_id, room_id, room_type, check_in_date, check_out_date, 
                   number_of_guests, total_price, status, special_requests)
                  VALUES 
                  (:customer_id, :room_id, :room_type, :check_in_date, :check_out_date,
                   :number_of_guests, :total_price, :status, :special_requests)";
        
        // STEP 2: Prepare the query (makes it safe from hackers)
        $stmt = $this->conn->prepare($query);
        
        // STEP 3: Fill in the blanks (bind values to placeholders)
        $stmt->bindParam(":customer_id", $this->customer_id);
        $stmt->bindParam(":room_id", $this->room_id);
        $stmt->bindParam(":room_type", $this->room_type);
        $stmt->bindParam(":check_in_date", $this->check_in_date);
        $stmt->bindParam(":check_out_date", $this->check_out_date);
        $stmt->bindParam(":number_of_guests", $this->number_of_guests);
        $stmt->bindParam(":total_price", $this->total_price);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":special_requests", $this->special_requests);
        
        // STEP 4: Execute the query (actually save to database)
        if ($stmt->execute()) {
            // STEP 5: Get the ID of the booking we just created
            $this->id = $this->conn->lastInsertId();
            return true;  // Success!
        }
        return false;  // Failed
    }    /**
     * GET AVAILABLE ROOMS FOR SPECIFIC DATES
     * This finds rooms that are available for booking on specific dates
     * 
     * How it works:
     * 1. Find rooms of the requested type
     * 2. That are available (status = 'available')
     * 3. That are NOT already booked during the requested dates
     * 
     * @param $roomType - Type of room (e.g., "Deluxe Room")
     * @param $checkIn - Check-in date
     * @param $checkOut - Check-out date
     * @return First available room or null if none found
     */
    public function getAvailableRoomsForDates($roomType, $checkIn, $checkOut) {
        // SQL query explanation:
        // SELECT r.* FROM rooms r - Get all room information
        // WHERE r.room_type = :room_type - Only rooms of the requested type
        // AND r.status = 'available' - Only available rooms
        // AND r.id NOT IN (...) - Exclude rooms that are already booked
        //   The subquery finds rooms with bookings that overlap with our dates
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
        
        // Prepare and execute query
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":room_type", $roomType);
        $stmt->bindParam(":check_in", $checkIn);
        $stmt->bindParam(":check_out", $checkOut);
        $stmt->execute();
        
        // Return first result (or null if no rooms found)
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
    }    /**
     * CALCULATE TOTAL PRICE
     * This calculates how much a booking costs
     * 
     * Formula: price per night × number of nights
     * 
     * @param $pricePerNight - Cost for one night
     * @param $checkIn - Check-in date
     * @param $checkOut - Check-out date
     * @return Total price (price × nights)
     */
    public function calculateTotalPrice($pricePerNight, $checkIn, $checkOut) {
        // STEP 1: Convert dates to DateTime objects (easier to work with)
        $checkInDate = new DateTime($checkIn);
        $checkOutDate = new DateTime($checkOut);
        
        // STEP 2: Calculate number of nights between dates
        $nights = $checkInDate->diff($checkOutDate)->days;
        
        // STEP 3: Multiply price per night by number of nights
        return $pricePerNight * $nights;
    }

    


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

    


    public function updateStatus() {
         
        $currentBooking = $this->getById($this->id);
        if (!$currentBooking) {
            return false;
        }
        
        $oldStatus = $currentBooking['status'];
        $roomId = $currentBooking['room_id'];
        
         
        $query = "UPDATE " . $this->table_name . "
                  SET status=:status, updated_at=NOW()
                  WHERE id=:id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id);
        
        if (!$stmt->execute()) {
            return false;
        }
        
         
        if ($roomId) {
             
            if ($this->status === 'cancelled' || $this->status === 'completed') {
                $this->updateRoomStatus($roomId, 'available');
            }
             
            elseif ($this->status === 'confirmed') {
                $this->updateRoomStatus($roomId, 'booked');
            }
             
             
        }
        
        return true;
    }

    


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

    


    public function delete() {
         
        $booking = $this->getById($this->id);
        
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        
        if ($stmt->execute()) {
             
            if ($booking && $booking['status'] === 'confirmed' && isset($booking['room_id'])) {
                $this->updateRoomStatus($booking['room_id'], 'available');
            }
            return true;
        }
        return false;
    }

    



    public function hasDateConflict($roomId, $checkIn, $checkOut, $excludeBookingId) {
        $query = "SELECT COUNT(*) as cnt
                  FROM " . $this->table_name . "
                  WHERE room_id = :room_id
                  AND id != :exclude_id
                  AND status IN ('pending', 'confirmed')
                  AND (check_in_date <= :check_out AND check_out_date >= :check_in)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":room_id", $roomId);
        $stmt->bindParam(":exclude_id", $excludeBookingId);
        $stmt->bindParam(":check_in", $checkIn);
        $stmt->bindParam(":check_out", $checkOut);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return isset($row['cnt']) && intval($row['cnt']) > 0;
    }
}
?>
