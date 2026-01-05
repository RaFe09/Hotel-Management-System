<?php

session_start();
require_once __DIR__ . '/../controllers/RoomController.php';

// Get room type from URL parameter
$roomType = isset($_GET['type']) ? urldecode($_GET['type']) : '';

// Validate room type
$validRoomTypes = ['Deluxe Room', 'Executive Suite', 'Presidential Suite', 'Romantic Suite'];
if (!in_array($roomType, $validRoomTypes)) {
    header('Location: rooms.php');
    exit;
}

$roomController = new RoomController();
$rooms = $roomController->getRoomsByType($roomType);

// Group rooms by status
$roomsByStatus = [
    'available' => [],
    'booked' => [],
    'maintenance' => []
];

foreach ($rooms as $room) {
    $roomsByStatus[$room['status']][] = $room;
}

// Function to get room image based on room type
function getRoomImage($roomType) {
    $imageMap = [
        'Deluxe Room' => 'Deluxe Room.png',
        'Executive Suite' => 'Executive Suite.jpg',
        'Presidential Suite' => 'Presidential Suite.jpg',
        'Romantic Suite' => 'Romantic Suite.jpg'
    ];
    
    $imageName = isset($imageMap[$roomType]) ? $imageMap[$roomType] : 'Deluxe Room.png';
    return '../../image/' . $imageName;
}

// Get price
$prices = array_column($rooms, 'price_per_night');
$price = $prices[0]; // All rooms of same type have same price

// Room type details and amenities
$roomDetails = [
    'Deluxe Room' => [
        'description' => 'Experience comfort and elegance in our spacious Deluxe Rooms, designed for both business and leisure travelers. These well-appointed rooms feature modern amenities and stunning views.',
        'size' => '350 sq ft',
        'bed' => '1 King Bed or 2 Queen Beds',
        'capacity' => '2-4 Guests',
        'amenities' => ['Free Wi-Fi', 'Flat-screen TV', 'Mini Bar', 'Work Desk', 'In-room Safe', 'Coffee/Tea Maker', 'Air Conditioning', 'Private Bathroom']
    ],
    'Executive Suite' => [
        'description' => 'Indulge in luxury with our Executive Suites, featuring separate living areas and premium furnishings. Perfect for extended stays or special occasions.',
        'size' => '550 sq ft',
        'bed' => '1 King Bed',
        'capacity' => '2-3 Guests',
        'amenities' => ['Free Wi-Fi', '55" Smart TV', 'Separate Living Area', 'Mini Bar', 'Work Desk', 'In-room Safe', 'Coffee/Tea Maker', 'Air Conditioning', 'Premium Bathroom', 'City View']
    ],
    'Presidential Suite' => [
        'description' => 'The ultimate in luxury accommodation. Our Presidential Suites offer unparalleled elegance, spaciousness, and world-class amenities for the most discerning guests.',
        'size' => '1,200 sq ft',
        'bed' => '1 King Bed',
        'capacity' => '2-4 Guests',
        'amenities' => ['Free Wi-Fi', '65" Smart TV', 'Separate Living & Dining Area', 'Full Kitchen', 'Premium Mini Bar', 'Executive Work Space', 'In-room Safe', 'Nespresso Machine', 'Air Conditioning', 'Jacuzzi', 'Panoramic Views', 'Butler Service Available']
    ],
    'Romantic Suite' => [
        'description' => 'Create unforgettable memories in our Romantic Suites, designed for couples seeking an intimate and luxurious escape. Featuring elegant décor and special romantic touches.',
        'size' => '650 sq ft',
        'bed' => '1 King Bed',
        'capacity' => '2 Guests',
        'amenities' => ['Free Wi-Fi', 'Smart TV', 'Romantic Décor', 'Jacuzzi Tub', 'Premium Mini Bar', 'Champagne Service', 'In-room Safe', 'Coffee/Tea Maker', 'Air Conditioning', 'Premium Bathroom', 'City View', 'Special Occasion Setup']
    ]
];

$details = isset($roomDetails[$roomType]) ? $roomDetails[$roomType] : $roomDetails['Deluxe Room'];
$availableCount = count($roomsByStatus['available']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($roomType); ?> - Grand Hotel</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="rooms-container">
        <header class="rooms-header">
            <h1>Grand Hotel</h1>
            <div class="header-actions">
                <a href="rooms.php" class="btn-back">← Back to Rooms</a>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="room-detail-hero">
            <div class="hero-image-wrapper">
                <img src="<?php echo getRoomImage($roomType); ?>" alt="<?php echo htmlspecialchars($roomType); ?>" class="hero-main-image">
            </div>
            <div class="hero-content">
                <h1 class="hero-title"><?php echo htmlspecialchars($roomType); ?></h1>
                <div class="hero-price">
                    <span class="price-amount">৳<?php echo number_format($price, 2); ?></span>
                    <span class="price-period">per night</span>
                </div>
                <?php if ($availableCount > 0): ?>
                    <div class="availability-badge available">
                        <span class="badge-icon">✓</span>
                        <span><?php echo $availableCount; ?> rooms available</span>
                    </div>
                <?php else: ?>
                    <div class="availability-badge unavailable">
                        <span class="badge-icon">!</span>
                        <span>Currently unavailable</span>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Room Details Section -->
        <section class="room-detail-content">
            <div class="detail-main">
                <!-- Description -->
                <div class="detail-section">
                    <h2 class="detail-section-title">Overview</h2>
                    <p class="room-description-text"><?php echo htmlspecialchars($details['description']); ?></p>
                </div>

                <!-- Room Specifications -->
                <div class="detail-section">
                    <h2 class="detail-section-title">Room Specifications</h2>
                    <div class="specs-grid">
                        <div class="spec-item">
                            <span class="spec-icon">📐</span>
                            <div class="spec-info">
                                <span class="spec-label">Room Size</span>
                                <span class="spec-value"><?php echo $details['size']; ?></span>
                            </div>
                        </div>
                        <div class="spec-item">
                            <span class="spec-icon">🛏️</span>
                            <div class="spec-info">
                                <span class="spec-label">Bed Type</span>
                                <span class="spec-value"><?php echo $details['bed']; ?></span>
                            </div>
                        </div>
                        <div class="spec-item">
                            <span class="spec-icon">👥</span>
                            <div class="spec-info">
                                <span class="spec-label">Capacity</span>
                                <span class="spec-value"><?php echo $details['capacity']; ?></span>
                            </div>
                        </div>
                        <div class="spec-item">
                            <span class="spec-icon">🏢</span>
                            <div class="spec-info">
                                <span class="spec-label">Floors</span>
                                <span class="spec-value">Multiple floors</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Amenities -->
                <div class="detail-section">
                    <h2 class="detail-section-title">Amenities & Features</h2>
                    <div class="amenities-grid">
                        <?php foreach ($details['amenities'] as $amenity): ?>
                            <div class="amenity-item">
                                <span class="amenity-icon">✓</span>
                                <span class="amenity-name"><?php echo htmlspecialchars($amenity); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="detail-sidebar">
                <div class="booking-card">
                    <div class="booking-header">
                        <h3>Book This Room</h3>
                    </div>
                    <div class="booking-content">
                        <div class="booking-price">
                            <span class="booking-price-amount">৳<?php echo number_format($price, 2); ?></span>
                            <span class="booking-price-period">per night</span>
                        </div>
                        <div class="booking-info">
                            <div class="info-row">
                                <span class="info-label">Total Rooms:</span>
                                <span class="info-value"><?php echo count($rooms); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Available:</span>
                                <span class="info-value available"><?php echo $availableCount; ?></span>
                            </div>
                        </div>
                        <?php if ($availableCount > 0): ?>
                            <a href="booking.php?room_type=<?php echo urlencode($roomType); ?>" class="btn-book-now">Book Now</a>
                        <?php else: ?>
                            <button class="btn-book-now disabled" disabled>Not Available</button>
                        <?php endif; ?>
                        <p class="booking-note">Contact us for reservations and special requests</p>
                    </div>
                </div>
            </div>
        </section>
    </div>
</body>
</html>

