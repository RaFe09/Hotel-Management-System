<?php

session_start();
require_once __DIR__ . '/../controllers/RoomController.php';

 
$roomType = isset($_GET['type']) ? urldecode($_GET['type']) : '';

 
$validRoomTypes = ['Deluxe Room', 'Executive Suite', 'Presidential Suite', 'Romantic Suite'];
if (!in_array($roomType, $validRoomTypes)) {
    header('Location: rooms.php');
    exit;
}

$roomController = new RoomController();
$rooms = $roomController->getRoomsByType($roomType);

 
$roomsByStatus = [
    'available' => [],
    'booked' => [],
    'maintenance' => []
];

foreach ($rooms as $room) {
    $roomsByStatus[$room['status']][] = $room;
}

 
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

 
$prices = array_column($rooms, 'price_per_night');
$price = $prices[0];  

 
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

 
$reviews = [];
$reviewsReady = false;
try {
    require_once __DIR__ . '/../../config/database.php';
    $db = new Database();
    $conn = $db->getConnection();
    $chk = $conn->prepare("SHOW TABLES LIKE 'reviews'");
    $chk->execute();
    $reviewsReady = $chk->rowCount() > 0;
    if ($reviewsReady) {
        $sql = "SELECT rv.*, c.first_name, c.last_name
                FROM reviews rv
                LEFT JOIN customers c ON rv.customer_id = c.id
                LEFT JOIN rooms r ON rv.room_id = r.id
                WHERE r.room_type = :type
                ORDER BY rv.created_at DESC
                LIMIT 20";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':type', $roomType);
        $stmt->execute();
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $reviews = [];
    $reviewsReady = false;
}
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

        
        <section class="room-detail-content">
            <div class="detail-main">
                
                <div class="detail-section">
                    <h2 class="detail-section-title">Overview</h2>
                    <p class="room-description-text"><?php echo htmlspecialchars($details['description']); ?></p>
                </div>

                
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

                
                <div class="detail-section">
                    <h2 class="detail-section-title">Reviews</h2>
                    <?php if (!$reviewsReady): ?>
                        <p style="color:#777;">(Reviews table not setup yet. Run <code>db/new_features.sql</code>)</p>
                    <?php elseif (empty($reviews)): ?>
                        <p style="color:#777;">No reviews yet.</p>
                    <?php else: ?>
                        <div style="display:grid; grid-template-columns: 1fr; gap: 10px;">
                            <?php foreach ($reviews as $rv): ?>
                                <div style="border:1px solid #eee; border-radius:10px; padding:12px; background:white;">
                                    <div style="display:flex; justify-content:space-between; gap:10px;">
                                        <strong>
                                            <?php echo htmlspecialchars(($rv['first_name'] ?? 'Guest') . ' ' . ($rv['last_name'] ?? '')); ?>
                                        </strong>
                                        <span>
                                            Rating: <strong><?php echo (int)$rv['rating']; ?>/5</strong>
                                        </span>
                                    </div>
                                    <?php if (!empty($rv['comment'])): ?>
                                        <div style="margin-top: 8px; color:#444;">
                                            <?php echo htmlspecialchars($rv['comment']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div style="margin-top: 8px; color:#777; font-size: 12px;">
                                        <?php echo date('M d, Y', strtotime($rv['created_at'])); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            
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

