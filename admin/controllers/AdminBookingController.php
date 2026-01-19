<?php
/**
 * ADMIN BOOKING CONTROLLER
 * 
 * WHAT IS THIS FILE?
 * This file handles all booking operations for the admin panel.
 * A controller is like a manager - it receives requests, talks to the database
 * through models, and sends responses back.
 * 
 * FOR BEGINNERS:
 * - Controller = Manager (organizes everything)
 * - Model = Worker (does the database work)
 * - View = Display (shows the HTML page)
 */

// Include the models we need
// Models are classes that talk to the database
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Room.php';
require_once __DIR__ . '/../models/Customer.php';

/**
 * AdminBookingController Class
 * This class manages all booking operations for admins
 */
class AdminBookingController {
    // These are our helpers - they talk to the database
    private $booking;    // Helper for booking operations
    private $room;       // Helper for room operations
    private $customer;   // Helper for customer operations

    /**
     * CONSTRUCTOR
     * This runs automatically when we create a new AdminBookingController
     * It creates our helper objects (booking, room, customer)
     */
    public function __construct() {
        // Create helper objects so we can use them
        $this->booking = new Booking();
        $this->room = new Room();
        $this->customer = new Customer();
    }

    /**
     * PROCESS BOOKING
     * This function creates a new booking for a customer
     * 
     * STEPS:
     * 1. Validate all the input data (check if everything is filled correctly)
     * 2. Handle customer (create new or use existing)
     * 3. Check if room is available
     * 4. Calculate total price
     * 5. Save booking to database
     * 
     * @param $data - Array containing booking information
     * @return Array with success status and either booking info or errors
     */
    public function processBooking($data) {
        $errors = []; // Array to store any error messages

        // STEP 1: VALIDATE ALL REQUIRED FIELDS
        // Check if room type is provided
        if (empty($data['room_type'])) {
            $errors[] = "Room type is required";
        }
        
        // Check if room ID is valid (must be a positive number)
        if (empty($data['room_id']) || intval($data['room_id']) <= 0) {
            $errors[] = "Room number is required";
        }
        
        // Check if check-in date is provided
        if (empty($data['check_in_date'])) {
            $errors[] = "Check-in date is required";
        }
        
        // Check if check-out date is provided
        if (empty($data['check_out_date'])) {
            $errors[] = "Check-out date is required";
        }
        
        // Check if number of guests is valid (at least 1)
        if (empty($data['number_of_guests']) || $data['number_of_guests'] < 1) {
            $errors[] = "Number of guests must be at least 1";
        }
        
        // STEP 2: VALIDATE CUSTOMER INFORMATION
        // If customer ID is not provided, we need customer details
        if (empty($data['customer_id'])) {
            // Check if all customer fields are filled
            if (empty($data['customer_email'])) {
                $errors[] = "Customer email is required";
            }
            if (empty($data['first_name'])) {
                $errors[] = "Customer first name is required";
            }
            if (empty($data['last_name'])) {
                $errors[] = "Customer last name is required";
            }
            if (empty($data['phone'])) {
                $errors[] = "Customer phone is required";
            }
        }

        // STEP 3: VALIDATE DATES
        // Make sure dates make sense (check-in not in past, check-out after check-in)
        if (!empty($data['check_in_date']) && !empty($data['check_out_date'])) {
            // Convert dates to DateTime objects (easier to compare)
            $checkIn = new DateTime($data['check_in_date']);
            $checkOut = new DateTime($data['check_out_date']);
            $today = new DateTime();
            $today->setTime(0, 0, 0); // Set time to midnight for accurate comparison

            // Check-in cannot be in the past
            if ($checkIn < $today) {
                $errors[] = "Check-in date cannot be in the past";
            }
            
            // Check-out must be after check-in
            if ($checkOut <= $checkIn) {
                $errors[] = "Check-out date must be after check-in date";
            }
        }

        // STEP 4: VALIDATE EMAIL FORMAT
        // Make sure email is in correct format (contains @ and .)
        if (!empty($data['customer_email']) && !filter_var($data['customer_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }

        // If we found any errors, stop here and return them
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // STEP 5: HANDLE CUSTOMER
        // We need to either use an existing customer or create a new one
        $customerId = null; // Will store the customer's ID

        // Option A: If customer ID is provided, use existing customer
        if (!empty($data['customer_id']) && is_numeric($data['customer_id'])) {
            // Try to find the customer in database
            $existingCustomer = $this->customer->getById($data['customer_id']);
            if ($existingCustomer) {
                // Customer found! Use their ID
                $customerId = $existingCustomer['id'];
            } else {
                // Customer not found - return error
                return ['success' => false, 'errors' => ['Selected customer not found.']];
            }
        } else {
            // Option B: No customer ID provided, so create new customer
            // First, check if customer with this email already exists
            $this->customer->email = $data['customer_email'];
            if ($this->customer->emailExists()) {
                // Customer already exists! Use their ID
                $customerId = $this->customer->id;
            } else {
                // Customer doesn't exist - create new customer
                $this->customer->first_name = $data['first_name'];
                $this->customer->last_name = $data['last_name'];
                $this->customer->email = $data['customer_email'];
                $this->customer->phone = $data['phone'];
                $this->customer->password = '';  // Empty password for admin-created customers

                // Try to save new customer to database
                if ($this->customer->create()) {
                    // Success! Get the new customer's ID
                    $customerId = $this->customer->id;
                } else {
                    // Failed to create customer
                    return ['success' => false, 'errors' => ['Failed to create customer. Please try again.']];
                }
            }
        }

        // STEP 6: CHECK IF ROOM IS AVAILABLE
        // Make sure the selected room is actually available for the requested dates
        $selectedRoom = $this->booking->getAvailableRoomByIdForDates(
            intval($data['room_id']),           // Room ID
            $data['room_type'],                  // Room type
            $data['check_in_date'],              // Check-in date
            $data['check_out_date']              // Check-out date
        );
        
        // If room is not available, return error
        if (!$selectedRoom) {
            return ['success' => false, 'errors' => ['Selected room is not available for the selected dates']];
        }

        // STEP 7: CALCULATE TOTAL PRICE
        // Calculate: price per night × number of nights
        $totalPrice = $this->booking->calculateTotalPrice(
            $selectedRoom['price_per_night'],    // Cost per night
            $data['check_in_date'],              // Check-in date
            $data['check_out_date']              // Check-out date
        );

        // STEP 8: CREATE THE BOOKING
        // Set all booking information
        $this->booking->customer_id = $customerId;                    // Which customer
        $this->booking->room_id = $selectedRoom['id'];                // Which room
        $this->booking->room_type = $data['room_type'];               // Room type
        $this->booking->check_in_date = $data['check_in_date'];       // Check-in date
        $this->booking->check_out_date = $data['check_out_date'];     // Check-out date
        $this->booking->number_of_guests = $data['number_of_guests']; // Number of guests
        $this->booking->total_price = $totalPrice;                    // Total price
        $this->booking->status = 'confirmed';                         // Status (confirmed = accepted)
        $this->booking->special_requests = $data['special_requests'] ?? ''; // Any special requests

        // Save booking to database
        if ($this->booking->create()) {
            // SUCCESS! Booking created
            // Update room status to "booked" so it can't be booked again
            $this->booking->updateRoomStatus($selectedRoom['id'], 'booked');
            
            // Return success message with booking details
            return [
                'success' => true,
                'booking_id' => $this->booking->id,           // Booking ID (for reference)
                'room_number' => $selectedRoom['room_number'], // Room number
                'customer_id' => $customerId,                  // Customer ID
                'message' => 'Booking confirmed successfully!'
            ];
        }

        // If we get here, booking creation failed
        return ['success' => false, 'errors' => ['Failed to create booking. Please try again.']];
    }

    
    /**
     * GET ROOM DETAILS FOR BOOKING
     * This function gets information about a specific room type
     * It returns the room type, price, and how many are available
     * 
     * @param $roomType - Type of room (e.g., "Deluxe Room")
     * @return Array with room details or null if not found
     */
    public function getRoomDetailsForBooking($roomType) {
        // Get all rooms of this type from database
        $rooms = $this->room->getByType($roomType);
        
        // If no rooms found, return null
        if (empty($rooms)) {
            return null;
        }
        
        // Get the first room (they all have same price and type)
        $room = $rooms[0];
        
        // Count how many rooms are available (status = 'available')
        $availableCount = count(array_filter($rooms, function($r) {
            return $r['status'] === 'available';
        }));

        // Return room information
        return [
            'room_type' => $room['room_type'],        // Type of room
            'price_per_night' => $room['price_per_night'], // Cost per night
            'available_count' => $availableCount      // How many available
        ];
    }

    


    public function getAllBookings() {
        return $this->booking->getAll();
    }

    


    public function getAllCustomers() {
        return $this->customer->getAll();
    }

    


    public function updateCustomer($id, $data) {
        $errors = [];

        if (empty($data['first_name'])) {
            $errors[] = "First name is required";
        }
        if (empty($data['last_name'])) {
            $errors[] = "Last name is required";
        }
        if (empty($data['email'])) {
            $errors[] = "Email is required";
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }
        if (empty($data['phone'])) {
            $errors[] = "Phone is required";
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $this->customer->id = $id;
        $this->customer->first_name = $data['first_name'];
        $this->customer->last_name = $data['last_name'];
        $this->customer->email = $data['email'];
        $this->customer->phone = $data['phone'];

        if ($this->customer->update()) {
            return ['success' => true, 'message' => 'Customer updated successfully'];
        }

        return ['success' => false, 'errors' => ['Failed to update customer']];
    }

    


    public function deleteCustomer($id) {
        $this->customer->id = $id;
        if ($this->customer->delete()) {
            return ['success' => true, 'message' => 'Customer deleted successfully'];
        }
        return ['success' => false, 'errors' => ['Failed to delete customer']];
    }

    


    public function updateBookingStatus($id, $status) {
        $validStatuses = ['pending', 'confirmed', 'cancelled', 'completed'];
        if (!in_array($status, $validStatuses)) {
            return ['success' => false, 'errors' => ['Invalid status']];
        }

        $this->booking->id = $id;
        $this->booking->status = $status;

        if ($this->booking->updateStatus()) {
            return ['success' => true, 'message' => 'Booking status updated successfully'];
        }

        return ['success' => false, 'errors' => ['Failed to update booking status']];
    }

    


    public function deleteBooking($id) {
        $this->booking->id = $id;
        if ($this->booking->delete()) {
            return ['success' => true, 'message' => 'Booking deleted successfully'];
        }
        return ['success' => false, 'errors' => ['Failed to delete booking']];
    }

    



    public function updateBooking($id, $data) {
        $errors = [];

        $booking = $this->booking->getById($id);
        if (!$booking) {
            return ['success' => false, 'errors' => ['Booking not found']];
        }

        $checkIn = $data['check_in_date'] ?? '';
        $checkOut = $data['check_out_date'] ?? '';
        $guests = intval($data['number_of_guests'] ?? 1);
        $status = $data['status'] ?? $booking['status'];
        $special = $data['special_requests'] ?? ($booking['special_requests'] ?? '');

        if (empty($checkIn)) $errors[] = "Check-in date is required";
        if (empty($checkOut)) $errors[] = "Check-out date is required";
        if ($guests < 1) $errors[] = "Number of guests must be at least 1";

        $validStatuses = ['pending', 'confirmed', 'cancelled', 'completed'];
        if (!in_array($status, $validStatuses)) {
            $errors[] = "Invalid status";
        }

        if (!empty($checkIn) && !empty($checkOut)) {
            try {
                $in = new DateTime($checkIn);
                $out = new DateTime($checkOut);
                if ($out <= $in) {
                    $errors[] = "Check-out date must be after check-in date";
                }
            } catch (Exception $e) {
                $errors[] = "Invalid date format";
            }
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

         
        $roomId = $booking['room_id'];
        if ($this->booking->hasDateConflict($roomId, $checkIn, $checkOut, $id)) {
            return ['success' => false, 'errors' => ['This room already has another booking in that date range']];
        }

         
        $room = $this->room->getById($roomId);
        if (!$room) {
            return ['success' => false, 'errors' => ['Room not found for this booking']];
        }
        $totalPrice = $this->booking->calculateTotalPrice($room['price_per_night'], $checkIn, $checkOut);

         
        $this->booking->id = $id;
        $this->booking->room_type = $booking['room_type'];  
        $this->booking->check_in_date = $checkIn;
        $this->booking->check_out_date = $checkOut;
        $this->booking->number_of_guests = $guests;
        $this->booking->total_price = $totalPrice;
        $this->booking->status = $status;
        $this->booking->special_requests = $special;

        if ($this->booking->update()) {
             
            if ($status === 'cancelled' || $status === 'completed') {
                $this->booking->updateRoomStatus($roomId, 'available');
            } elseif ($status === 'confirmed') {
                $this->booking->updateRoomStatus($roomId, 'booked');
            }
            return ['success' => true, 'message' => 'Booking updated successfully'];
        }

        return ['success' => false, 'errors' => ['Failed to update booking']];
    }

    


    public function getCustomerById($id) {
        return $this->customer->getById($id);
    }

    


    public function getBookingById($id) {
        return $this->booking->getById($id);
    }

    


    public function searchCustomers($searchTerm) {
        return $this->customer->search($searchTerm);
    }
}
?>
