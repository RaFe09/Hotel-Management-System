<?php

session_start();
require_once __DIR__ . '/../controllers/AdminAuthController.php';
require_once __DIR__ . '/../controllers/AdminBookingController.php';

AdminAuthController::requireLogin();

$controller = new AdminBookingController();
$message = '';
$error = '';

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $result = $controller->deleteBooking($_GET['delete']);
    if ($result['success']) {
        $message = $result['message'];
    } else {
        $error = implode(', ', $result['errors']);
    }
}

// Handle status update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update_status') {
    $result = $controller->updateBookingStatus($_POST['booking_id'], $_POST['status']);
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
    <title>Manage Bookings - Grand Hotel</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 90%;
            max-width: 500px;
            border-radius: 8px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: black;
        }
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <div class="header-content">
                <h1>Manage Bookings</h1>
                <div class="header-actions">
                    <span class="admin-name">Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                    <a href="dashboard.php" class="btn btn-outline">Dashboard</a>
                    <a href="customers.php" class="btn btn-outline">Customers</a>
                    <a href="book-room.php" class="btn btn-primary">New Booking</a>
                    <a href="logout.php" class="btn btn-outline">Logout</a>
                </div>
            </div>
        </header>

        <main class="admin-main">
            <?php if ($message): ?>
                <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 15px; margin: 20px 0; border-radius: 5px;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error" style="background: #f8d7da; color: #721c24; padding: 15px; margin: 20px 0; border-radius: 5px;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="dashboard-section">
                <div class="section-header">
                    <h2>All Bookings</h2>
                    <p>Total: <?php echo count($bookings); ?> bookings</p>
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
                                <th>Total Price</th>
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
                                            <span class="status-badge status-<?php echo strtolower($booking['status']); ?>">
                                                <?php echo ucfirst($booking['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button onclick="openStatusModal(<?php echo $booking['id']; ?>, '<?php echo htmlspecialchars($booking['status']); ?>')" 
                                                        class="btn btn-primary btn-small">Change Status</button>
                                                <a href="?delete=<?php echo $booking['id']; ?>" 
                                                   class="btn btn-danger btn-small" 
                                                   onclick="return confirm('Are you sure you want to delete this booking? This action cannot be undone.');">Delete</a>
                                            </div>
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

    <!-- Status Update Modal -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeStatusModal()">&times;</span>
            <h2>Update Booking Status</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="booking_id" id="status_booking_id">
                
                <div class="form-group">
                    <label for="status_select">Status</label>
                    <select id="status_select" name="status" required>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn btn-primary">Update Status</button>
                    <button type="button" class="btn btn-outline" onclick="closeStatusModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openStatusModal(bookingId, currentStatus) {
            document.getElementById('status_booking_id').value = bookingId;
            document.getElementById('status_select').value = currentStatus;
            document.getElementById('statusModal').style.display = 'block';
        }

        function closeStatusModal() {
            document.getElementById('statusModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('statusModal');
            if (event.target == modal) {
                closeStatusModal();
            }
        }
    </script>
</body>
</html>

