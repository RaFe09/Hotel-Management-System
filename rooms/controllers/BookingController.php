<?php

require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Room.php';

class BookingController {
    private $booking;
    private $room;

    public function __construct() {
        $this->booking = new Booking();
        $this->room = new Room();
    }

    /**
     * Process booking submission
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
        if (empty($data['customer_id'])) {
            $errors[] = "Customer must be logged in";
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

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
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
        $this->booking->customer_id = $data['customer_id'];
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
}
?>

