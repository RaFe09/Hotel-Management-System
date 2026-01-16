<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login_signup/php/login.php?redirect=feedback');
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
    $chk = $conn->prepare("SHOW TABLES LIKE 'complaints'");
    $chk->execute();
    $tableReady = $chk->rowCount() > 0;
} catch (Exception $e) {
    $tableReady = false;
}

 
$myBookings = [];
try {
    $stmt = $conn->prepare("SELECT id, room_type, check_in_date, check_out_date
                            FROM bookings
                            WHERE customer_id = :cid
                            ORDER BY created_at DESC");
    $stmt->bindValue(':cid', $customerId);
    $stmt->execute();
    $myBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $myBookings = [];
}

if ($tableReady && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? 'complaint';
    $subject = trim($_POST['subject'] ?? '');
    $msg = trim($_POST['message'] ?? '');
    $bookingId = intval($_POST['booking_id'] ?? 0);

    $validTypes = ['complaint', 'feedback'];
    if (!in_array($type, $validTypes)) $type = 'complaint';

    if (empty($subject)) {
        $error = "Subject is required";
    } elseif (empty($msg)) {
        $error = "Message is required";
    } else {
        $realBookingId = null;
        if ($bookingId > 0) {
             
            foreach ($myBookings as $b) {
                if ((int)$b['id'] === $bookingId) {
                    $realBookingId = $bookingId;
                    break;
                }
            }
        }

        try {
            $stmt = $conn->prepare("INSERT INTO complaints (customer_id, booking_id, type, subject, message, status)
                                    VALUES (:cid, :bid, :type, :subject, :message, 'pending')");
            $stmt->bindValue(':cid', $customerId);
            $stmt->bindValue(':bid', $realBookingId);
            $stmt->bindValue(':type', $type);
            $stmt->bindValue(':subject', $subject);
            $stmt->bindValue(':message', $msg);
            $stmt->execute();
            $message = "Submitted successfully!";
        } catch (Exception $e) {
            $error = "Failed to submit";
        }
    }
}

 
$items = [];
if ($tableReady) {
    try {
        $stmt = $conn->prepare("SELECT * FROM complaints WHERE customer_id = :cid ORDER BY created_at DESC");
        $stmt->bindValue(':cid', $customerId);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $items = [];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complaints & Feedback - Grand Hotel</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .box { background: white; border: 1px solid #eee; border-radius: 10px; padding: 15px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 10px; }
        .inp { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; }
        .item { background: white; border: 1px solid #eee; border-radius: 10px; padding: 12px; }
        .status { display:inline-block; padding: 4px 10px; border-radius: 999px; font-size: 12px; background:#f2f2f2; }
    </style>
</head>
<body>
    <div class="rooms-container">
        <header class="rooms-header">
            <h1>Grand Hotel</h1>
            <h2>Complaints & Feedback</h2>
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
                <p>Please run <code>db/new_features.sql</code> (complaints table).</p>
            </div>
        <?php else: ?>
            <div class="box">
                <h3>Submit</h3>
                <form method="POST">
                    <div class="grid">
                        <div>
                            <label>Type</label>
                            <select class="inp" name="type">
                                <option value="complaint">Complaint</option>
                                <option value="feedback">Feedback</option>
                            </select>
                        </div>
                        <div>
                            <label>Related Booking (optional)</label>
                            <select class="inp" name="booking_id">
                                <option value="0">No booking</option>
                                <?php foreach ($myBookings as $b): ?>
                                    <option value="<?php echo (int)$b['id']; ?>">#<?php echo (int)$b['id']; ?> - <?php echo htmlspecialchars($b['room_type']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <label>Subject</label>
                            <input class="inp" type="text" name="subject" required>
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <label>Message</label>
                            <textarea class="inp" name="message" rows="4" required></textarea>
                        </div>
                    </div>
                    <button class="btn btn-primary" type="submit" style="margin-top: 10px;">Submit</button>
                </form>
            </div>

            <div style="margin-top: 15px;">
                <h3>Your Previous Items</h3>
                <?php if (empty($items)): ?>
                    <div class="item">No items yet.</div>
                <?php else: ?>
                    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 12px;">
                        <?php foreach ($items as $it): ?>
                            <div class="item">
                                <div style="display:flex; justify-content:space-between; gap:10px;">
                                    <strong>#<?php echo (int)$it['id']; ?> - <?php echo htmlspecialchars($it['type']); ?></strong>
                                    <span class="status"><?php echo htmlspecialchars($it['status']); ?></span>
                                </div>
                                <div style="margin-top: 6px;">
                                    <strong><?php echo htmlspecialchars($it['subject']); ?></strong>
                                </div>
                                <div style="margin-top: 6px; color:#555;">
                                    <?php echo htmlspecialchars($it['message']); ?>
                                </div>
                                <?php if (!empty($it['admin_reply'])): ?>
                                    <div style="margin-top: 8px; color:#333;">
                                        <strong>Admin reply:</strong> <?php echo htmlspecialchars($it['admin_reply']); ?>
                                    </div>
                                <?php endif; ?>
                                <div style="margin-top: 8px; color:#777; font-size: 12px;">
                                    <?php echo date('M d, Y H:i', strtotime($it['created_at'])); ?>
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

