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

    


    public function processBooking($data) {
        $errors = [];

         
        if (empty($data['room_type'])) {
            $errors[] = "Room type is required";
        }
        if (empty($data['room_id']) || intval($data['room_id']) <= 0) {
            $errors[] = "Room number is required";
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

         
        if (!empty($data['customer_email']) && !filter_var($data['customer_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

         
        $customerId = null;

         
        if (!empty($data['customer_id']) && is_numeric($data['customer_id'])) {
            $existingCustomer = $this->customer->getById($data['customer_id']);
            if ($existingCustomer) {
                $customerId = $existingCustomer['id'];
            } else {
                return ['success' => false, 'errors' => ['Selected customer not found.']];
            }
        } else {
             
            $this->customer->email = $data['customer_email'];
            if ($this->customer->emailExists()) {
                 
                $customerId = $this->customer->id;
            } else {
                 
                $this->customer->first_name = $data['first_name'];
                $this->customer->last_name = $data['last_name'];
                $this->customer->email = $data['customer_email'];
                $this->customer->phone = $data['phone'];
                $this->customer->password = '';  

                if ($this->customer->create()) {
                    $customerId = $this->customer->id;
                } else {
                    return ['success' => false, 'errors' => ['Failed to create customer. Please try again.']];
                }
            }
        }

         
        $selectedRoom = $this->booking->getAvailableRoomByIdForDates(
            intval($data['room_id']),
            $data['room_type'],
            $data['check_in_date'],
            $data['check_out_date']
        );
        if (!$selectedRoom) {
            return ['success' => false, 'errors' => ['Selected room is not available for the selected dates']];
        }

         
        $totalPrice = $this->booking->calculateTotalPrice(
            $selectedRoom['price_per_night'],
            $data['check_in_date'],
            $data['check_out_date']
        );

         
        $this->booking->customer_id = $customerId;
        $this->booking->room_id = $selectedRoom['id'];
        $this->booking->room_type = $data['room_type'];
        $this->booking->check_in_date = $data['check_in_date'];
        $this->booking->check_out_date = $data['check_out_date'];
        $this->booking->number_of_guests = $data['number_of_guests'];
        $this->booking->total_price = $totalPrice;
        $this->booking->status = 'confirmed';
        $this->booking->special_requests = $data['special_requests'] ?? '';

        if ($this->booking->create()) {
             
            $this->booking->updateRoomStatus($selectedRoom['id'], 'booked');
            
            return [
                'success' => true,
                'booking_id' => $this->booking->id,
                'room_number' => $selectedRoom['room_number'],
                'customer_id' => $customerId,
                'message' => 'Booking confirmed successfully!'
            ];
        }

        return ['success' => false, 'errors' => ['Failed to create booking. Please try again.']];
    }

    


    public function getRoomDetailsForBooking($roomType) {
        $rooms = $this->room->getByType($roomType);
        if (empty($rooms)) {
            return null;
        }
        
         
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
