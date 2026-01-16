<?php

session_start();
require_once __DIR__ . '/../controllers/RoomController.php';

$roomController = new RoomController();
$roomsByType = $roomController->getRoomsByTypeGrouped();
$statistics = $roomController->getStatistics();

 
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

 
function getRoomTypeSlug($roomType) {
    return strtolower(str_replace(' ', '-', $roomType));
}

 
function getPriceRange($rooms) {
    if (empty($rooms)) {
        return 'N/A';
    }
    $prices = array_column($rooms, 'price_per_night');
    $minPrice = min($prices);
    $maxPrice = max($prices);
    
    if ($minPrice == $maxPrice) {
        return '৳' . number_format($minPrice, 2) . '/night';
    }
    return '৳' . number_format($minPrice, 2) . ' - ৳' . number_format($maxPrice, 2) . '/night';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rooms - Grand Hotel</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="rooms-container">
        <header class="rooms-header">
            <h1>Grand Hotel</h1>
            <h2>Room Management</h2>
            <div class="header-actions">
                <a href="../../landing/php/index.php" class="btn-back">← Back to Home</a>
                <a href="search.php" class="btn-back" style="margin-left: 10px;">Search Rooms</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="my-bookings.php" class="btn-back" style="margin-left: 10px;">My Bookings</a>
                <?php endif; ?>
            </div>
        </header>

        
        <section class="statistics-section">
            <div class="stat-card stat-total">
                <div class="stat-icon">🏨</div>
                <div class="stat-info">
                    <h3>Total Rooms</h3>
                    <p class="stat-number"><?php echo $statistics['total']; ?></p>
                </div>
            </div>
            <div class="stat-card stat-available">
                <div class="stat-icon">✅</div>
                <div class="stat-info">
                    <h3>Available</h3>
                    <p class="stat-number"><?php echo $statistics['available']; ?></p>
                </div>
            </div>
            <div class="stat-card stat-booked">
                <div class="stat-icon">📅</div>
                <div class="stat-info">
                    <h3>Booked</h3>
                    <p class="stat-number"><?php echo $statistics['booked']; ?></p>
                </div>
            </div>
            <div class="stat-card stat-maintenance">
                <div class="stat-icon">🔧</div>
                <div class="stat-info">
                    <h3>Maintenance</h3>
                    <p class="stat-number"><?php echo $statistics['maintenance']; ?></p>
                </div>
            </div>
        </section>

        
        <section class="room-types-section">
            <h2 class="section-title">Choose Your Room Type</h2>
            <div class="room-types-grid">
                <?php 
                $roomTypes = [
                    'Deluxe Room' => ['icon' => '🛏️', 'color' => '#4caf50'],
                    'Executive Suite' => ['icon' => '🏢', 'color' => '#2196f3'],
                    'Presidential Suite' => ['icon' => '👑', 'color' => '#ff9800'],
                    'Romantic Suite' => ['icon' => '💕', 'color' => '#e91e63']
                ];
                
                foreach ($roomTypes as $roomType => $info): 
                    $rooms = isset($roomsByType[$roomType]) ? $roomsByType[$roomType] : [];
                    $availableCount = count(array_filter($rooms, function($r) { return $r['status'] === 'available'; }));
                    $totalCount = count($rooms);
                ?>
                    <a href="room-details.php?type=<?php echo urlencode($roomType); ?>" class="room-type-card">
                        <div class="room-type-image-container">
                            <img src="<?php echo getRoomImage($roomType); ?>" alt="<?php echo htmlspecialchars($roomType); ?>" class="room-type-image">
                        </div>
                        <div class="room-type-content">
                            <div class="room-type-header">
                                <span class="room-type-icon"><?php echo $info['icon']; ?></span>
                                <h3 class="room-type-name"><?php echo htmlspecialchars($roomType); ?></h3>
                            </div>
                            <div class="room-type-stats">
                                <div class="room-type-stat">
                                    <span class="stat-label">Total Rooms:</span>
                                    <span class="stat-value"><?php echo $totalCount; ?></span>
                                </div>
                                <div class="room-type-stat">
                                    <span class="stat-label">Available:</span>
                                    <span class="stat-value available"><?php echo $availableCount; ?></span>
                                </div>
                            </div>
                            <div class="room-type-price">
                                <?php echo getPriceRange($rooms); ?>
                            </div>
                            <div class="room-type-action">
                                <span class="view-details-btn">View Details →</span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</body>
</html>

