<?php

require_once __DIR__ . '/../models/Room.php';

class RoomController {
    private $room;

    public function __construct() {
        $this->room = new Room();
    }

    /**
     * Get all rooms grouped by status
     */
    public function getAllRooms() {
        return [
            'available' => $this->room->getAvailable(),
            'booked' => $this->room->getBooked(),
            'maintenance' => $this->room->getMaintenance(),
            'statistics' => $this->room->getStatistics()
        ];
    }

    /**
     * Get room statistics
     */
    public function getStatistics() {
        return $this->room->getStatistics();
    }

    /**
     * Get rooms by status
     */
    public function getRoomsByStatus($status) {
        $validStatuses = ['available', 'booked', 'maintenance'];
        if (!in_array($status, $validStatuses)) {
            return [];
        }
        return $this->room->getByStatus($status);
    }

    /**
     * Get rooms by type
     */
    public function getRoomsByType($roomType) {
        return $this->room->getByType($roomType);
    }

    /**
     * Get all rooms grouped by type
     */
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

