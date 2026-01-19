<?php
session_start();
require_once __DIR__ . '/../controllers/AdminAuthController.php';
require_once __DIR__ . '/../controllers/AdminBookingController.php';

AdminAuthController::requireLogin();

$controller = new AdminBookingController();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $result = $controller->updateBookingStatus($_POST['booking_id'], $_POST['status']);
    if ($result['success']) {
        $message = $result['message'];
    } else {
        $error = implode(', ', $result['errors']);
    }
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $result = $controller->deleteBooking($_GET['delete']);
    if ($result['success']) {
        $message = $result['message'];
    } else {
        $error = implode(', ', $result['errors']);
    }
}

$bookings = $controller->getAllBookings();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <div class="header-content">
                <h1>Manage Bookings</h1>
                <div class="header-actions">
                    <a href="dashboard.php" class="btn btn-outline">Dashboard</a>
                    <a href="book-room.php" class="btn btn-primary">New Booking</a>
                    <a href="logout.php" class="btn btn-outline">Logout</a>
                </div>
            </div>
        </header>

        <main class="admin-main">
            <?php if ($message): ?>
                <div style="background: #d4edda; color: #155724; padding: 15px; margin: 20px 0; border-radius: 5px;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div style="background: #f8d7da; color: #721c24; padding: 15px; margin: 20px 0; border-radius: 5px;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="dashboard-section">
                <div class="section-header">
                    <h2>All Bookings (<?php echo count($bookings); ?>)</h2>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Customer</th>
                                <th>Room</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Guests</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($bookings)): ?>
                                <tr>
                                    <td colspan="9" class="text-center">No bookings found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($bookings as $booking): ?>
                                    <tr>
                                        <td>#<?php echo $booking['id']; ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?><br>
                                            <small><?php echo htmlspecialchars($booking['email']); ?></small>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($booking['room_type']); ?><br>
                                            <small>Room <?php echo htmlspecialchars($booking['room_number'] ?? 'N/A'); ?></small>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($booking['check_in_date'])); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($booking['check_out_date'])); ?></td>
                                        <td><?php echo $booking['number_of_guests']; ?></td>
                                        <td>৳<?php echo number_format($booking['total_price'], 2); ?></td>
                                        <td>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                <select name="status" onchange="this.form.submit()" style="padding:5px;">
                                                    <option value="pending" <?php echo ($booking['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="confirmed" <?php echo ($booking['status'] == 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                                                    <option value="cancelled" <?php echo ($booking['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                                    <option value="completed" <?php echo ($booking['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                                                </select>
                                                <input type="hidden" name="update_status" value="1">
                                            </form>
                                        </td>
                                        <td>
                                            <a href="?delete=<?php echo $booking['id']; ?>" 
                                               onclick="return confirm('Delete this booking?');"
                                               style="color:#dc3545; text-decoration:none; padding:5px 10px; border:1px solid #dc3545; border-radius:3px; font-size:12px;">
                                                Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
