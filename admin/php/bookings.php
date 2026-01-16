<?php

session_start();
require_once __DIR__ . '/../controllers/AdminAuthController.php';
require_once __DIR__ . '/../controllers/AdminBookingController.php';

AdminAuthController::requireLogin();

$controller = new AdminBookingController();
$message = '';
$error = '';

 
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $result = $controller->deleteBooking($_GET['delete']);
    if ($result['success']) {
        $message = $result['message'];
    } else {
        $error = implode(', ', $result['errors']);
    }
}

 
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update_status') {
    $result = $controller->updateBookingStatus($_POST['booking_id'], $_POST['status']);
    if ($result['success']) {
        $message = $result['message'];
    } else {
        $error = implode(', ', $result['errors']);
    }
}

 
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update_booking') {
    $bookingId = intval($_POST['booking_id'] ?? 0);
    if ($bookingId > 0) {
        $result = $controller->updateBooking($bookingId, $_POST);
        if ($result['success']) {
            $message = $result['message'];
        } else {
            $error = implode(', ', $result['errors']);
        }
    } else {
        $error = "Invalid booking id";
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
        .special-requests-cell {
            max-width: 260px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
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
                                <th>Special Requests</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($bookings)): ?>
                                <tr>
                                    <td colspan="10" class="text-center">No bookings found</td>
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
                                        <td class="special-requests-cell" title="<?php echo htmlspecialchars($booking['special_requests'] ?? ''); ?>">
                                            <?php if (!empty($booking['special_requests'])): ?>
                                                <?php echo htmlspecialchars($booking['special_requests']); ?>
                                            <?php else: ?>
                                                <span style="color:#999;">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($booking)); ?>)" 
                                                        class="btn btn-outline btn-small">Edit</button>
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

    
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>Edit Booking</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="update_booking">
                <input type="hidden" name="booking_id" id="edit_booking_id">

                <div class="form-group">
                    <label for="edit_check_in">Check-in Date</label>
                    <input type="date" id="edit_check_in" name="check_in_date" required>
                </div>

                <div class="form-group">
                    <label for="edit_check_out">Check-out Date</label>
                    <input type="date" id="edit_check_out" name="check_out_date" required>
                </div>

                <div class="form-group">
                    <label for="edit_guests">Guests</label>
                    <input type="number" id="edit_guests" name="number_of_guests" min="1" max="10" required>
                </div>

                <div class="form-group">
                    <label for="edit_status">Status</label>
                    <select id="edit_status" name="status" required>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="completed">Completed (Checked-out)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="edit_special">Special Requests</label>
                    <textarea id="edit_special" name="special_requests" rows="3"></textarea>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <button type="button" class="btn btn-outline" onclick="closeEditModal()">Cancel</button>
                </div>
            </form>
            <p style="margin-top:10px; color:#777; font-size: 13px;">
                Note: This keeps the same room, and blocks obvious date conflicts.
            </p>
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

        function openEditModal(booking) {
            document.getElementById('edit_booking_id').value = booking.id;
            document.getElementById('edit_check_in').value = booking.check_in_date;
            document.getElementById('edit_check_out').value = booking.check_out_date;
            document.getElementById('edit_guests').value = booking.number_of_guests;
            document.getElementById('edit_status').value = booking.status;
            document.getElementById('edit_special').value = booking.special_requests || '';
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('statusModal');
            const editModal = document.getElementById('editModal');
            if (event.target == modal) {
                closeStatusModal();
            }
            if (event.target == editModal) {
                closeEditModal();
            }
        }
    </script>
</body>
</html>

