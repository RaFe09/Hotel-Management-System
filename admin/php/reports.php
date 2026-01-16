<?php
session_start();
require_once __DIR__ . '/../controllers/AdminAuthController.php';
require_once __DIR__ . '/../models/Room.php';
require_once __DIR__ . '/../models/Booking.php';

AdminAuthController::requireLogin();

$roomModel = new Room();
$bookingModel = new Booking();

$reportType = $_GET['type'] ?? 'monthly';
$validTypes = ['daily', 'monthly', 'yearly'];
if (!in_array($reportType, $validTypes)) $reportType = 'monthly';

$roomStats = $roomModel->getStatistics();
$totalRooms = max(1, intval($roomStats['total'] ?? 0));
$bookedRooms = intval($roomStats['booked'] ?? 0);
$occupancyRate = round(($bookedRooms / $totalRooms) * 100, 2);

 
$allBookings = $bookingModel->getAll();
$bookingCounts = ['pending' => 0, 'confirmed' => 0, 'cancelled' => 0, 'completed' => 0];
$totalRevenue = 0;
foreach ($allBookings as $b) {
    $st = $b['status'] ?? '';
    if (isset($bookingCounts[$st])) $bookingCounts[$st]++;
    if ($st === 'confirmed' || $st === 'completed') {
        $totalRevenue += floatval($b['total_price'] ?? 0);
    }
}

 
$popularTypes = [];
foreach ($allBookings as $b) {
    $t = $b['room_type'] ?? 'Unknown';
    if (!isset($popularTypes[$t])) $popularTypes[$t] = 0;
    $popularTypes[$t]++;
}
arsort($popularTypes);

 
$db = new Database();
$conn = $db->getConnection();

$rows = [];
try {
    if ($reportType === 'daily') {
        $q = "SELECT DATE(created_at) as label,
                     COUNT(*) as bookings,
                     SUM(total_price) as revenue
              FROM bookings
              WHERE status IN ('confirmed','completed')
              AND created_at >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
              GROUP BY DATE(created_at)
              ORDER BY label DESC";
    } elseif ($reportType === 'yearly') {
        $q = "SELECT YEAR(created_at) as label,
                     COUNT(*) as bookings,
                     SUM(total_price) as revenue
              FROM bookings
              WHERE status IN ('confirmed','completed')
              GROUP BY YEAR(created_at)
              ORDER BY label DESC";
    } else {
        $q = "SELECT DATE_FORMAT(created_at, '%Y-%m') as label,
                     COUNT(*) as bookings,
                     SUM(total_price) as revenue
              FROM bookings
              WHERE status IN ('confirmed','completed')
              AND created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
              GROUP BY DATE_FORMAT(created_at, '%Y-%m')
              ORDER BY label DESC";
    }

    $stmt = $conn->prepare($q);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $rows = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <div class="header-content">
                <h1>Revenue & Analytics</h1>
                <div class="header-actions">
                    <span class="admin-name">Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                    <a href="dashboard.php" class="btn btn-outline">Dashboard</a>
                    <a href="logout.php" class="btn btn-outline">Logout</a>
                </div>
            </div>
        </header>

        <main class="admin-main">
            <div class="stats-grid" style="margin-top: 10px;">
                <div class="stat-card">
                    <div class="stat-icon booking">💰</div>
                    <div class="stat-content">
                        <h3>৳<?php echo number_format($totalRevenue, 2); ?></h3>
                        <p>Total Revenue</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon available">📊</div>
                    <div class="stat-content">
                        <h3><?php echo count($allBookings); ?></h3>
                        <p>Total Bookings</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon booked">🏷️</div>
                    <div class="stat-content">
                        <h3><?php echo $occupancyRate; ?>%</h3>
                        <p>Occupancy (current)</p>
                    </div>
                </div>
            </div>

            <div class="dashboard-section">
                <div class="section-header">
                    <h2>Booking Status Summary</h2>
                </div>
                <div style="display:flex; gap: 12px; flex-wrap: wrap;">
                    <div class="stat-card" style="flex:1; min-width: 180px;">
                        <div class="stat-content">
                            <h3><?php echo $bookingCounts['pending']; ?></h3>
                            <p>Pending</p>
                        </div>
                    </div>
                    <div class="stat-card" style="flex:1; min-width: 180px;">
                        <div class="stat-content">
                            <h3><?php echo $bookingCounts['confirmed']; ?></h3>
                            <p>Confirmed</p>
                        </div>
                    </div>
                    <div class="stat-card" style="flex:1; min-width: 180px;">
                        <div class="stat-content">
                            <h3><?php echo $bookingCounts['completed']; ?></h3>
                            <p>Completed (Checked-out)</p>
                        </div>
                    </div>
                    <div class="stat-card" style="flex:1; min-width: 180px;">
                        <div class="stat-content">
                            <h3><?php echo $bookingCounts['cancelled']; ?></h3>
                            <p>Cancelled</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dashboard-section">
                <div class="section-header">
                    <h2>Revenue Report</h2>
                    <div style="display:flex; gap: 10px;">
                        <a class="btn btn-outline btn-small" href="reports.php?type=daily">Daily</a>
                        <a class="btn btn-outline btn-small" href="reports.php?type=monthly">Monthly</a>
                        <a class="btn btn-outline btn-small" href="reports.php?type=yearly">Yearly</a>
                    </div>
                </div>

                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th><?php echo ucfirst($reportType); ?></th>
                                <th>Bookings</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($rows)): ?>
                                <tr><td colspan="3" class="text-center">No report data found</td></tr>
                            <?php else: ?>
                                <?php foreach ($rows as $r): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($r['label']); ?></td>
                                        <td><?php echo (int)$r['bookings']; ?></td>
                                        <td>৳<?php echo number_format((float)$r['revenue'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="dashboard-section">
                <div class="section-header">
                    <h2>Popular Room Types</h2>
                    <p>Based on bookings count</p>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Room Type</th>
                                <th>Bookings</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($popularTypes)): ?>
                                <tr><td colspan="2" class="text-center">No data</td></tr>
                            <?php else: ?>
                                <?php foreach (array_slice($popularTypes, 0, 8, true) as $type => $cnt): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($type); ?></td>
                                        <td><?php echo (int)$cnt; ?></td>
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

