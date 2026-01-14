<?php

session_start();
require_once __DIR__ . '/../controllers/AdminAuthController.php';
require_once __DIR__ . '/../controllers/AdminBookingController.php';
require_once __DIR__ . '/../models/Room.php';

AdminAuthController::requireLogin();

$bookingController = new AdminBookingController();
$room = new Room();

// Get statistics
$roomStats = $room->getStatistics();
$allBookings = $bookingController->getAllBookings();
$recentBookings = array_slice($allBookings, 0, 5); // Get 5 most recent
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Grand Hotel</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <div class="header-content">
                <h1>Grand Hotel - Admin Dashboard</h1>
                <div class="header-actions">
                    <span class="admin-name">Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                    <a href="book-room.php" class="btn btn-primary">Book Room</a>
                    <a href="bookings.php" class="btn btn-outline">Manage Bookings</a>
                    <a href="customers.php" class="btn btn-outline">Manage Customers</a>
                    <a href="logout.php" class="btn btn-outline">Logout</a>
                </div>
            </div>
        </header>

        <main class="admin-main">
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon available">🏨</div>
                    <div class="stat-content">
                        <h3><?php echo $roomStats['total']; ?></h3>
                        <p>Total Rooms</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon available">✅</div>
                    <div class="stat-content">
                        <h3><?php echo $roomStats['available']; ?></h3>
                        <p>Available</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon booked">📦</div>
                    <div class="stat-content">
                        <h3><?php echo $roomStats['booked']; ?></h3>
                        <p>Booked</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon maintenance">🔧</div>
                    <div class="stat-content">
                        <h3><?php echo $roomStats['maintenance']; ?></h3>
                        <p>Maintenance</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon booking">📋</div>
                    <div class="stat-content">
                        <h3><?php echo count($allBookings); ?></h3>
                        <p>Total Bookings</p>
                    </div>
                </div>
            </div>

            <!-- Recent Bookings -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2>Recent Bookings</h2>
                    <div style="display: flex; gap: 10px;">
                        <a href="book-room.php" class="btn btn-primary btn-small">New Booking</a>
                        <a href="bookings.php" class="btn btn-outline btn-small">View All Bookings</a>
                    </div>
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
                            <?php if (empty($recentBookings)): ?>
                                <tr>
                                    <td colspan="9" class="text-center">No bookings found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentBookings as $booking): ?>
                                    <tr>
                                        <td>#<?php echo $booking['id']; ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?><br>
                                            <small><?php echo htmlspecialchars($booking['email']); ?></small>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($booking['room_type']); ?><br>
                                            <small>Room <?php echo htmlspecialchars($booking['room_number']); ?></small>
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
                                            <a href="bookings.php" class="btn btn-primary btn-small">Manage</a>
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
