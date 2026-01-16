<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login_signup/php/login.php?redirect=review');
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$db = new Database();
$conn = $db->getConnection();

$customerId = intval($_SESSION['user_id']);
$bookingId = intval($_GET['booking_id'] ?? ($_POST['booking_id'] ?? 0));

$message = '';
$error = '';

 
$tableReady = false;
try {
    $chk = $conn->prepare("SHOW TABLES LIKE 'reviews'");
    $chk->execute();
    $tableReady = $chk->rowCount() > 0;
} catch (Exception $e) {
    $tableReady = false;
}

 
$booking = null;
if ($bookingId > 0) {
    try {
        $stmt = $conn->prepare("SELECT b.*, r.room_number
                                FROM bookings b
                                LEFT JOIN rooms r ON b.room_id = r.id
                                WHERE b.id = :bid AND b.customer_id = :cid
                                LIMIT 1");
        $stmt->bindValue(':bid', $bookingId);
        $stmt->bindValue(':cid', $customerId);
        $stmt->execute();
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $booking = null;
    }
}

 
if ($tableReady && $_SERVER['REQUEST_METHOD'] === 'POST' && ($bookingId > 0)) {
    $rating = intval($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');

    if (!$booking) {
        $error = "Booking not found";
    } elseif ($rating < 1 || $rating > 5) {
        $error = "Rating must be between 1 and 5";
    } else {
         
        $today = date('Y-m-d');
        if (!empty($booking['check_out_date']) && $booking['check_out_date'] > $today) {
            $error = "You can review after checkout date";
        } else {
            try {
                 
                $chk2 = $conn->prepare("SELECT id FROM reviews WHERE booking_id = :bid AND customer_id = :cid LIMIT 1");
                $chk2->bindValue(':bid', $bookingId);
                $chk2->bindValue(':cid', $customerId);
                $chk2->execute();
                if ($chk2->rowCount() > 0) {
                    $error = "You already reviewed this booking";
                } else {
                    $stmt = $conn->prepare("INSERT INTO reviews (customer_id, booking_id, room_id, rating, comment)
                                            VALUES (:cid, :bid, :rid, :rating, :comment)");
                    $stmt->bindValue(':cid', $customerId);
                    $stmt->bindValue(':bid', $bookingId);
                    $stmt->bindValue(':rid', $booking['room_id']);
                    $stmt->bindValue(':rating', $rating);
                    $stmt->bindValue(':comment', $comment);
                    $stmt->execute();
                    $message = "Thanks! Review submitted.";
                }
            } catch (Exception $e) {
                $error = "Failed to submit review";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Booking - Grand Hotel</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .box { background: white; border: 1px solid #eee; border-radius: 10px; padding: 15px; max-width: 800px; margin: 0 auto; }
        .inp { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="rooms-container">
        <header class="rooms-header">
            <h1>Grand Hotel</h1>
            <h2>Review & Rating</h2>
            <div class="header-actions">
                <a href="my-bookings.php" class="btn-back">← Back to My Bookings</a>
            </div>
        </header>

        <div class="box">
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
                <h3>Setup Needed</h3>
                <p>Please run <code>db/new_features.sql</code> (reviews table).</p>
            <?php elseif (!$booking): ?>
                <h3>Booking not found</h3>
                <p>Open this page from <strong>My Bookings</strong>.</p>
            <?php else: ?>
                <h3>Booking #<?php echo (int)$booking['id']; ?></h3>
                <p>
                    Room: <strong><?php echo htmlspecialchars($booking['room_type']); ?></strong>
                    (Room <?php echo htmlspecialchars($booking['room_number'] ?? 'N/A'); ?>)
                </p>
                <p>Dates: <?php echo htmlspecialchars($booking['check_in_date']); ?> → <?php echo htmlspecialchars($booking['check_out_date']); ?></p>

                <form method="POST">
                    <input type="hidden" name="booking_id" value="<?php echo (int)$bookingId; ?>">
                    <div class="form-group">
                        <label>Rating (1-5)</label>
                        <select class="inp" name="rating" required>
                            <option value="5">5 - Excellent</option>
                            <option value="4">4 - Good</option>
                            <option value="3">3 - Okay</option>
                            <option value="2">2 - Bad</option>
                            <option value="1">1 - Very Bad</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Comment (optional)</label>
                        <textarea class="inp" name="comment" rows="4" placeholder="Write your feedback..."></textarea>
                    </div>
                    <button class="btn btn-primary" type="submit">Submit Review</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

