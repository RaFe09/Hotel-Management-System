<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login_signup/php/login.php?redirect=service-requests');
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$db = new Database();
$conn = $db->getConnection();
$customerId = intval($_SESSION['user_id']);

$message = '';
$error = '';

 
$tableReady = false;
try {
    $chk = $conn->prepare("SHOW TABLES LIKE 'service_requests'");
    $chk->execute();
    $tableReady = $chk->rowCount() > 0;
} catch (Exception $e) {
    $tableReady = false;
}

 
$myBookings = [];
try {
    $stmt = $conn->prepare("SELECT b.id, b.room_type, b.check_in_date, b.check_out_date, r.id as room_id, r.room_number
                            FROM bookings b
                            LEFT JOIN rooms r ON b.room_id = r.id
                            WHERE b.customer_id = :cid
                            ORDER BY b.created_at DESC");
    $stmt->bindValue(':cid', $customerId);
    $stmt->execute();
    $myBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $myBookings = [];
}

 
if ($tableReady && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $type = $_POST['request_type'] ?? '';
    $desc = trim($_POST['description'] ?? '');
    $bookingId = intval($_POST['booking_id'] ?? 0);

    $validTypes = ['room_service', 'housekeeping', 'maintenance'];
    if (!in_array($type, $validTypes)) {
        $error = "Invalid request type";
    } elseif (empty($desc)) {
        $error = "Description is required";
    } else {
        $roomId = null;
        $realBookingId = null;
        if ($bookingId > 0) {
             
            foreach ($myBookings as $b) {
                if ((int)$b['id'] === $bookingId) {
                    $realBookingId = $bookingId;
                    $roomId = $b['room_id'] ?? null;
                    break;
                }
            }
        }

        try {
            $sql = "INSERT INTO service_requests (customer_id, booking_id, room_id, request_type, description, status)
                    VALUES (:cid, :bid, :rid, :type, :desc, 'pending')";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':cid', $customerId);
            $stmt->bindValue(':bid', $realBookingId);
            $stmt->bindValue(':rid', $roomId);
            $stmt->bindValue(':type', $type);
            $stmt->bindValue(':desc', $desc);
            $stmt->execute();
            $message = "Service request submitted!";
        } catch (Exception $e) {
            $error = "Failed to create request";
        }
    }
}

 
$requests = [];
if ($tableReady) {
    try {
        $sql = "SELECT sr.*, s.full_name as staff_name, r.room_number
                FROM service_requests sr
                LEFT JOIN staff s ON sr.assigned_staff_id = s.id
                LEFT JOIN rooms r ON sr.room_id = r.id
                WHERE sr.customer_id = :cid
                ORDER BY sr.created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':cid', $customerId);
        $stmt->execute();
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $requests = [];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Requests - Grand Hotel</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .box { background: white; border: 1px solid #eee; border-radius: 10px; padding: 15px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 10px; }
        .inp { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; }
        .req { border: 1px solid #eee; border-radius: 10px; padding: 12px; background: white; }
        .status { display:inline-block; padding: 4px 10px; border-radius: 999px; font-size: 12px; background:#f2f2f2; }
    </style>
</head>
<body>
    <div class="rooms-container">
        <header class="rooms-header">
            <h1>Grand Hotel</h1>
            <h2>Service Requests</h2>
            <div class="header-actions">
                <a href="../../landing/php/index.php" class="btn-back">← Back to Home</a>
                <a href="my-bookings.php" class="btn-back" style="margin-left:10px;">My Bookings</a>
            </div>
        </header>

        <?php if ($message): ?>
            <div class="booking-success" style="padding: 10px; margin-bottom: 10px;">
                <p><?php echo htmlspecialchars($message); ?></p>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="error-messages" style="margin-bottom: 10px;">
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            </div>
        <?php endif; ?>

        <?php if (!$tableReady): ?>
            <div class="box">
                <h3>Setup Needed</h3>
                <p>The <code>service_requests</code> table is not created yet.</p>
                <p>Please run: <code>db/new_features.sql</code> in phpMyAdmin.</p>
            </div>
        <?php else: ?>
            <div class="box">
                <h3>Create a Request</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="create">
                    <div class="grid">
                        <div>
                            <label>Request Type</label>
                            <select class="inp" name="request_type" required>
                                <option value="room_service">Room Service</option>
                                <option value="housekeeping">Housekeeping</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>
                        <div>
                            <label>Related Booking (optional)</label>
                            <select class="inp" name="booking_id">
                                <option value="0">No booking</option>
                                <?php foreach ($myBookings as $b): ?>
                                    <option value="<?php echo (int)$b['id']; ?>">
                                        #<?php echo (int)$b['id']; ?> - <?php echo htmlspecialchars($b['room_type']); ?> (Room <?php echo htmlspecialchars($b['room_number'] ?? 'N/A'); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <label>Description</label>
                            <textarea class="inp" name="description" rows="4" placeholder="Write your request..." required></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" style="margin-top: 10px;">Submit Request</button>
                </form>
            </div>

            <div style="margin-top: 15px;">
                <h3>Your Requests</h3>
                <?php if (empty($requests)): ?>
                    <div class="req">No requests yet.</div>
                <?php else: ?>
                    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 12px;">
                        <?php foreach ($requests as $r): ?>
                            <div class="req">
                                <div style="display:flex; justify-content:space-between; gap:10px;">
                                    <strong>#<?php echo (int)$r['id']; ?></strong>
                                    <span class="status"><?php echo htmlspecialchars($r['status']); ?></span>
                                </div>
                                <div style="margin-top: 6px;">
                                    <div><strong>Type:</strong> <?php echo htmlspecialchars($r['request_type']); ?></div>
                                    <div><strong>Room:</strong> <?php echo htmlspecialchars($r['room_number'] ?? 'N/A'); ?></div>
                                    <div class="muted" style="color:#666; font-size: 13px; margin-top: 6px;">
                                        <?php echo htmlspecialchars($r['description']); ?>
                                    </div>
                                </div>
                                <div style="margin-top: 8px; font-size: 13px; color:#555;">
                                    <strong>Assigned Staff:</strong> <?php echo htmlspecialchars($r['staff_name'] ?? 'Not assigned'); ?>
                                </div>
                                <?php if (!empty($r['staff_notes'])): ?>
                                    <div style="margin-top: 8px; font-size: 13px; color:#444;">
                                        <strong>Staff Note:</strong> <?php echo htmlspecialchars($r['staff_notes']); ?>
                                    </div>
                                <?php endif; ?>
                                <div style="margin-top: 8px; font-size: 12px; color:#777;">
                                    Created: <?php echo date('M d, Y H:i', strtotime($r['created_at'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

