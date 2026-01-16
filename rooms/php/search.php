<?php
session_start();

require_once __DIR__ . '/../../config/database.php';

$db = new Database();
$conn = $db->getConnection();

 
$types = [];
try {
    $stmt = $conn->prepare("SELECT DISTINCT room_type FROM rooms ORDER BY room_type");
    $stmt->execute();
    $types = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $types = [];
}

$filters = [
    'room_type' => $_GET['room_type'] ?? '',
    'min_price' => $_GET['min_price'] ?? '',
    'max_price' => $_GET['max_price'] ?? '',
    'check_in' => $_GET['check_in'] ?? '',
    'check_out' => $_GET['check_out'] ?? '',
    'keyword' => $_GET['keyword'] ?? ''
];

$rooms = [];

 
if (isset($_GET['search'])) {
    $where = [];
    $params = [];

     
    $where[] = "r.status = 'available'";

    if (!empty($filters['room_type'])) {
        $where[] = "r.room_type = :room_type";
        $params[':room_type'] = $filters['room_type'];
    }
    if ($filters['min_price'] !== '') {
        $where[] = "r.price_per_night >= :min_price";
        $params[':min_price'] = floatval($filters['min_price']);
    }
    if ($filters['max_price'] !== '') {
        $where[] = "r.price_per_night <= :max_price";
        $params[':max_price'] = floatval($filters['max_price']);
    }
    if (!empty(trim($filters['keyword']))) {
        $where[] = "(r.description LIKE :kw OR r.room_type LIKE :kw OR r.room_number LIKE :kw)";
        $params[':kw'] = '%' . trim($filters['keyword']) . '%';
    }

     
    $dateFilter = "";
    if (!empty($filters['check_in']) && !empty($filters['check_out'])) {
        $dateFilter = "AND r.id NOT IN (
            SELECT b.room_id FROM bookings b
            WHERE b.status IN ('pending','confirmed')
            AND (b.check_in_date <= :check_out AND b.check_out_date >= :check_in)
        )";
        $params[':check_in'] = $filters['check_in'];
        $params[':check_out'] = $filters['check_out'];
    }

    $sql = "SELECT r.* FROM rooms r
            WHERE " . implode(" AND ", $where) . "
            " . $dateFilter . "
            ORDER BY r.room_type, r.floor_number, r.room_number";

    try {
        $stmt = $conn->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $rooms = [];
    }
}

$minDate = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Rooms - Grand Hotel</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .search-box { background: white; border: 1px solid #eee; border-radius: 10px; padding: 15px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 10px; }
        .inp { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; }
        .room-result { background: white; border: 1px solid #eee; border-radius: 10px; padding: 12px; }
        .room-result h3 { margin: 0 0 6px 0; }
        .pill { display:inline-block; padding: 4px 8px; border-radius: 999px; background: #f2f2f2; font-size: 12px; margin-right: 6px; }
    </style>
</head>
<body>
    <div class="rooms-container">
        <header class="rooms-header">
            <h1>Grand Hotel</h1>
            <h2>Search Rooms</h2>
            <div class="header-actions">
                <a href="rooms.php" class="btn-back">← Back to Rooms</a>
            </div>
        </header>

        <div class="search-box">
            <form method="GET">
                <div class="grid">
                    <div>
                        <label>Room Type</label>
                        <select class="inp" name="room_type">
                            <option value="">All types</option>
                            <?php foreach ($types as $t): ?>
                                <option value="<?php echo htmlspecialchars($t); ?>" <?php echo ($filters['room_type'] === $t) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($t); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label>Min Price</label>
                        <input class="inp" type="number" name="min_price" value="<?php echo htmlspecialchars($filters['min_price']); ?>" step="0.01" min="0" placeholder="0">
                    </div>
                    <div>
                        <label>Max Price</label>
                        <input class="inp" type="number" name="max_price" value="<?php echo htmlspecialchars($filters['max_price']); ?>" step="0.01" min="0" placeholder="99999">
                    </div>
                    <div>
                        <label>Check-in</label>
                        <input class="inp" type="date" name="check_in" value="<?php echo htmlspecialchars($filters['check_in']); ?>" min="<?php echo $minDate; ?>">
                    </div>
                    <div>
                        <label>Check-out</label>
                        <input class="inp" type="date" name="check_out" value="<?php echo htmlspecialchars($filters['check_out']); ?>" min="<?php echo $minDate; ?>">
                    </div>
                    <div>
                        <label>Keyword (amenities/notes)</label>
                        <input class="inp" type="text" name="keyword" value="<?php echo htmlspecialchars($filters['keyword']); ?>" placeholder="wifi, jacuzzi, etc.">
                    </div>
                </div>

                <div style="margin-top: 12px; display:flex; gap: 10px;">
                    <button class="btn btn-primary" type="submit" name="search" value="1">Search</button>
                    <a class="btn btn-outline" href="search.php">Reset</a>
                </div>
                <p style="margin-top: 10px; color:#777;">
                    Tip: If you enter dates, it only shows rooms that are free for that date range.
                </p>
            </form>
        </div>

        <div style="margin-top: 15px;">
            <?php if (!isset($_GET['search'])): ?>
                <p style="color:#666;">Use the filters above and click Search.</p>
            <?php else: ?>
                <h3 style="margin-bottom: 10px;">Results: <?php echo count($rooms); ?> room(s)</h3>
                <?php if (empty($rooms)): ?>
                    <div class="room-result">No rooms found.</div>
                <?php else: ?>
                    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 12px;">
                        <?php foreach ($rooms as $r): ?>
                            <div class="room-result">
                                <h3><?php echo htmlspecialchars($r['room_type']); ?></h3>
                                <div class="pill">Room <?php echo htmlspecialchars($r['room_number']); ?></div>
                                <div class="pill">Floor <?php echo (int)$r['floor_number']; ?></div>
                                <div style="margin-top: 8px;">
                                    <strong>৳<?php echo number_format((float)$r['price_per_night'], 2); ?></strong> / night
                                </div>
                                <div style="margin-top: 8px; color:#666; font-size: 13px;">
                                    <?php echo !empty($r['description']) ? htmlspecialchars($r['description']) : 'No description'; ?>
                                </div>
                                <div style="margin-top: 10px;">
                                    <a class="btn btn-primary btn-small" href="booking.php?room_type=<?php echo urlencode($r['room_type']); ?>">Book this type</a>
                                    <a class="btn btn-outline btn-small" href="room-details.php?type=<?php echo urlencode($r['room_type']); ?>">View details</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

