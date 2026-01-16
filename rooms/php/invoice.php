<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login_signup/php/login.php?redirect=invoice');
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$bookingId = intval($_GET['booking_id'] ?? 0);
if ($bookingId <= 0) {
    echo "Invalid booking id";
    exit;
}

$db = new Database();
$conn = $db->getConnection();

$customerId = intval($_SESSION['user_id']);

 
$booking = null;
$extras = ['breakfast' => 0, 'parking' => 0, 'airport_pickup' => 0, 'extra_total' => 0];
try {
    $sql = "SELECT b.*, c.first_name, c.last_name, c.email, c.phone, r.room_number
            FROM bookings b
            LEFT JOIN customers c ON b.customer_id = c.id
            LEFT JOIN rooms r ON b.room_id = r.id
            WHERE b.id = :bid AND b.customer_id = :cid
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':bid', $bookingId);
    $stmt->bindValue(':cid', $customerId);
    $stmt->execute();
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($booking) {
         
        $chk = $conn->prepare("SHOW TABLES LIKE 'booking_services'");
        $chk->execute();
        if ($chk->rowCount() > 0) {
            $s2 = $conn->prepare("SELECT * FROM booking_services WHERE booking_id = :bid LIMIT 1");
            $s2->bindValue(':bid', $bookingId);
            $s2->execute();
            $row = $s2->fetch(PDO::FETCH_ASSOC);
            if ($row) $extras = $row;
        }
    }
} catch (Exception $e) {
    $booking = null;
}

if (!$booking) {
    echo "Invoice not found (or not your booking).";
    exit;
}

$extrasTotal = floatval($extras['extra_total'] ?? 0);
$roomTotal = floatval($booking['total_price'] ?? 0);
$grandTotal = $roomTotal + $extrasTotal;

header('Content-Type: text/html; charset=UTF-8');
header('Content-Disposition: attachment; filename="invoice-' . $bookingId . '.html"');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo $bookingId; ?></title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; color: #222; }
        .box { border: 1px solid #ddd; border-radius: 10px; padding: 16px; max-width: 800px; margin: 0 auto; }
        h1 { margin: 0 0 6px 0; }
        .muted { color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { padding: 10px; border-bottom: 1px solid #eee; text-align: left; }
        th { background: #fafafa; }
        .right { text-align: right; }
        .total { font-size: 18px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="box">
        <h1>Grand Hotel - Invoice</h1>
        <div class="muted">Invoice #: <?php echo $bookingId; ?> | Date: <?php echo date('Y-m-d'); ?></div>

        <h3 style="margin-top: 16px;">Customer</h3>
        <div>
            <strong><?php echo htmlspecialchars(($booking['first_name'] ?? '') . ' ' . ($booking['last_name'] ?? '')); ?></strong><br>
            Email: <?php echo htmlspecialchars($booking['email'] ?? ''); ?><br>
            Phone: <?php echo htmlspecialchars($booking['phone'] ?? ''); ?>
        </div>

        <h3 style="margin-top: 16px;">Booking Details</h3>
        <div>
            Room Type: <strong><?php echo htmlspecialchars($booking['room_type']); ?></strong><br>
            Room Number: <?php echo htmlspecialchars($booking['room_number'] ?? 'N/A'); ?><br>
            Check-in: <?php echo htmlspecialchars($booking['check_in_date']); ?><br>
            Check-out: <?php echo htmlspecialchars($booking['check_out_date']); ?><br>
            Guests: <?php echo (int)$booking['number_of_guests']; ?><br>
            Status: <?php echo htmlspecialchars($booking['status']); ?>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Room Charge</td>
                    <td class="right">৳<?php echo number_format($roomTotal, 2); ?></td>
                </tr>
                <tr>
                    <td>Breakfast</td>
                    <td class="right"><?php echo ($extras['breakfast'] ?? 0) ? '৳500.00' : '৳0.00'; ?></td>
                </tr>
                <tr>
                    <td>Parking</td>
                    <td class="right"><?php echo ($extras['parking'] ?? 0) ? '৳300.00' : '৳0.00'; ?></td>
                </tr>
                <tr>
                    <td>Airport Pickup</td>
                    <td class="right"><?php echo ($extras['airport_pickup'] ?? 0) ? '৳1500.00' : '৳0.00'; ?></td>
                </tr>
                <tr>
                    <td class="total">Grand Total</td>
                    <td class="right total">৳<?php echo number_format($grandTotal, 2); ?></td>
                </tr>
            </tbody>
        </table>

        <p class="muted" style="margin-top: 14px;">
            This invoice is generated for an educational project.
        </p>
    </div>
</body>
</html>

