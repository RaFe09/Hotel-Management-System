<?php

require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Room.php';
require_once __DIR__ . '/../models/Customer.php';

class AdminBookingController {
    private $booking;
    private $room;
    private $customer;

    public function __construct() {
        $this->booking = new Booking();
        $this->room = new Room();
        $this->customer = new Customer();
    }

    /**
     * Process booking for customer (create customer if needed)
     */
    public function processBooking($data) {
        $errors = [];

        // Validate required fields
        if (empty($data['room_type'])) {
            $errors[] = "Room type is required";
        }
        if (empty($data['check_in_date'])) {
            $errors[] = "Check-in date is required";
        }
        if (empty($data['check_out_date'])) {
            $errors[] = "Check-out date is required";
        }
        if (empty($data['number_of_guests']) || $data['number_of_guests'] < 1) {
            $errors[] = "Number of guests must be at least 1";
        }
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

        // Validate dates
        if (!empty($data['check_in_date']) && !empty($data['check_out_date'])) {
            $checkIn = new DateTime($data['check_in_date']);
            $checkOut = new DateTime($data['check_out_date']);
            $today = new DateTime();
            $today->setTime(0, 0, 0);

            if ($checkIn < $today) {
                $errors[] = "Check-in date cannot be in the past";
            }
            if ($checkOut <= $checkIn) {
                $errors[] = "Check-out date must be after check-in date";
            }
        }

        // Validate email format
        if (!empty($data['customer_email']) && !filter_var($data['customer_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Find or create customer
        $this->customer->email = $data['customer_email'];
        $customerId = null;

        if ($this->customer->emailExists()) {
            // Customer exists, use their ID
            $customerId = $this->customer->id;
        } else {
            // Create new customer
            $this->customer->first_name = $data['first_name'];
            $this->customer->last_name = $data['last_name'];
            $this->customer->email = $data['customer_email'];
            $this->customer->phone = $data['phone'];
            $this->customer->password = ''; // Will generate random password in create method

            if ($this->customer->create()) {
                $customerId = $this->customer->id;
            } else {
                return ['success' => false, 'errors' => ['Failed to create customer. Please try again.']];
            }
        }

        // Get available room
        $availableRoom = $this->booking->getAvailableRoomsForDates(
            $data['room_type'],
            $data['check_in_date'],
            $data['check_out_date']
        );

        if (!$availableRoom) {
            return ['success' => false, 'errors' => ['No available rooms for the selected dates']];
        }

        // Calculate total price
        $totalPrice = $this->booking->calculateTotalPrice(
            $availableRoom['price_per_night'],
            $data['check_in_date'],
            $data['check_out_date']
        );

        // Create booking
        $this->booking->customer_id = $customerId;
        $this->booking->room_id = $availableRoom['id'];
        $this->booking->room_type = $data['room_type'];
        $this->booking->check_in_date = $data['check_in_date'];
        $this->booking->check_out_date = $data['check_out_date'];
        $this->booking->number_of_guests = $data['number_of_guests'];
        $this->booking->total_price = $totalPrice;
        $this->booking->status = 'confirmed';
        $this->booking->special_requests = $data['special_requests'] ?? '';

        if ($this->booking->create()) {
            // Update room status
            $this->booking->updateRoomStatus($availableRoom['id'], 'booked');
            
            return [
                'success' => true,
                'booking_id' => $this->booking->id,
                'room_number' => $availableRoom['room_number'],
                'customer_id' => $customerId,
                'message' => 'Booking confirmed successfully!'
            ];
        }

        return ['success' => false, 'errors' => ['Failed to create booking. Please try again.']];
    }

    /**
     * Get room details for booking
     */
    public function getRoomDetailsForBooking($roomType) {
        $rooms = $this->room->getByType($roomType);
        if (empty($rooms)) {
            return null;
        }
        
        // Get price from first room (all same type have same price)
        $room = $rooms[0];
        $availableCount = count(array_filter($rooms, function($r) {
            return $r['status'] === 'available';
        }));

        return [
            'room_type' => $room['room_type'],
            'price_per_night' => $room['price_per_night'],
            'available_count' => $availableCount
        ];
    }

    /**
     * Get all bookings
     */
    public function getAllBookings() {
        return $this->booking->getAll();
    }

    /**
     * Get all customers
     */
    public function getAllCustomers() {
        return $this->customer->getAll();
    }

    /**
     * Update customer
     */
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

    /**
     * Delete customer
     */
    public function deleteCustomer($id) {
        $this->customer->id = $id;
        if ($this->customer->delete()) {
            return ['success' => true, 'message' => 'Customer deleted successfully'];
        }
        return ['success' => false, 'errors' => ['Failed to delete customer']];
    }

    /**
     * Update booking status
     */
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

    /**
     * Delete booking
     */
    public function deleteBooking($id) {
        $this->booking->id = $id;
        if ($this->booking->delete()) {
            return ['success' => true, 'message' => 'Booking deleted successfully'];
        }
        return ['success' => false, 'errors' => ['Failed to delete booking']];
    }

    /**
     * Get customer by ID
     */
    public function getCustomerById($id) {
        return $this->customer->getById($id);
    }

    /**
     * Get booking by ID
     */
    public function getBookingById($id) {
        return $this->booking->getById($id);
    }
}
?>
