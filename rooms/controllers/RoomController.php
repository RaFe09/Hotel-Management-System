<?php

require_once __DIR__ . '/../models/Room.php';

class RoomController {
    private $room;

    public function __construct() {
        $this->room = new Room();
    }

    


    public function getAllRooms() {
        return [
            'available' => $this->room->getAvailable(),
            'booked' => $this->room->getBooked(),
            'maintenance' => $this->room->getMaintenance(),
            'statistics' => $this->room->getStatistics()
        ];
    }

    


    public function getStatistics() {
        return $this->room->getStatistics();
    }

    


    public function getRoomsByStatus($status) {
        $validStatuses = ['available', 'booked', 'maintenance'];
        if (!in_array($status, $validStatuses)) {
            return [];
        }
        return $this->room->getByStatus($status);
    }

    


    public function getRoomsByType($roomType) {
        return $this->room->getByType($roomType);
    }

    


    public function getRoomsByTypeGrouped() {
        $roomTypes = ['Deluxe Room', 'Executive Suite', 'Presidential Suite', 'Romantic Suite'];
        $result = [];
        
        foreach ($roomTypes as $type) {
            $result[$type] = $this->room->getByType($type);
        }
        
        return $result;
    }
}
?>

