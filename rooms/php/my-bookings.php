<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login_signup/php/login.php?redirect=my-bookings');
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$db = new Database();
$conn = $db->getConnection();

$customerId = intval($_SESSION['user_id']);

$bookings = [];
try {
     
    $hasExtras = false;
    $chk = $conn->prepare("SHOW TABLES LIKE 'booking_services'");
    $chk->execute();
    $hasExtras = $chk->rowCount() > 0;

    if ($hasExtras) {
        $sql = "SELECT b.*, r.room_number,
                       bs.breakfast, bs.parking, bs.airport_pickup, bs.extra_total
                FROM bookings b
                LEFT JOIN rooms r ON b.room_id = r.id
                LEFT JOIN booking_services bs ON bs.booking_id = b.id
                WHERE b.customer_id = :cid
                ORDER BY b.created_at DESC";
    } else {
        $sql = "SELECT b.*, r.room_number
                FROM bookings b
                LEFT JOIN rooms r ON b.room_id = r.id
                WHERE b.customer_id = :cid
                ORDER BY b.created_at DESC";
    }
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':cid', $customerId);
    $stmt->execute();
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $bookings = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Grand Hotel</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .table-container { background: white; border: 1px solid #eee; border-radius: 10px; overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; border-bottom: 1px solid #f1f1f1; text-align: left; }
        th { background: #fafafa; }
        .status { display:inline-block; padding: 4px 10px; border-radius: 999px; font-size: 12px; background:#f2f2f2; }
    </style>
</head>
<body>
    <div class="rooms-container">
        <header class="rooms-header">
            <h1>Grand Hotel</h1>
            <h2>My Bookings</h2>
            <div class="header-actions">
                <a href="../../landing/php/index.php" class="btn-back">← Back to Home</a>
                <a href="rooms.php" class="btn-back" style="margin-left: 10px;">Rooms</a>
                <a href="service-requests.php" class="btn-back" style="margin-left: 10px;">Service Requests</a>
                <a href="feedback.php" class="btn-back" style="margin-left: 10px;">Feedback</a>
            </div>
        </header>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Room</th>
                        <th>Dates</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Invoice</th>
                        <th>Review</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bookings)): ?>
                        <tr><td colspan="7">No bookings found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($bookings as $b): ?>
                            <?php
                                $extras = floatval($b['extra_total'] ?? 0);
                                $grand = floatval($b['total_price'] ?? 0) + $extras;
                                $today = date('Y-m-d');
                                $canReview = (!empty($b['check_out_date']) && $b['check_out_date'] <= $today);
                            ?>
                            <tr>
                                <td>#<?php echo (int)$b['id']; ?></td>
                                <td>
                                    <?php echo htmlspecialchars($b['room_type']); ?><br>
                                    <small>Room <?php echo htmlspecialchars($b['room_number'] ?? 'N/A'); ?></small>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($b['check_in_date']); ?> → <?php echo htmlspecialchars($b['check_out_date']); ?>
                                </td>
                                <td>
                                    ৳<?php echo number_format($grand, 2); ?>
                                    <?php if ($extras > 0): ?><br><small style="color:#666;">(includes extras)</small><?php endif; ?>
                                </td>
                                <td><span class="status"><?php echo htmlspecialchars($b['status']); ?></span></td>
                                <td>
                                    <a class="btn btn-outline btn-small" href="invoice.php?booking_id=<?php echo (int)$b['id']; ?>">Download</a>
                                </td>
                                <td>
                                    <?php if ($canReview): ?>
                                        <a class="btn btn-primary btn-small" href="review.php?booking_id=<?php echo (int)$b['id']; ?>">Review</a>
                                    <?php else: ?>
                                        <span style="color:#777; font-size: 12px;">After checkout</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

