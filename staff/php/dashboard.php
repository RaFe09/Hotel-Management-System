<?php

session_start();
require_once __DIR__ . '/../controllers/StaffAuthController.php';
require_once __DIR__ . '/../controllers/StaffRoomController.php';
require_once __DIR__ . '/../../config/database.php';

StaffAuthController::requireLogin();

$roomController = new StaffRoomController();

 
$canBookRoom = false;
try {
    $db = new Database();
    $conn = $db->getConnection();
    $col = $conn->prepare("SHOW COLUMNS FROM staff LIKE 'role'");
    $col->execute();
    if ($col->rowCount() > 0) {
        $sid = intval($_SESSION['staff_id']);
        $stmt = $conn->prepare("SELECT role FROM staff WHERE id = :id LIMIT 1");
        $stmt->bindValue(':id', $sid);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $roleNorm = strtolower(trim((string)($row['role'] ?? '')));
        $canBookRoom = in_array($roleNorm, ['receptionist', 'reception', 'frontdesk', 'front_desk', 'front desk'], true);
    }
} catch (Exception $e) {
    $canBookRoom = false;
}

 
$roomStats = $roomController->getRoomStatistics();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - Grand Hotel</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <div class="header-content">
                <h1>Grand Hotel - Staff Dashboard</h1>
                <div class="header-actions">
                    <span class="admin-name">Welcome, <?php echo htmlspecialchars($_SESSION['staff_name']); ?></span>
                    <?php if ($canBookRoom): ?>
                        <a href="../../admin/php/book-room.php" class="btn btn-primary">Book Room</a>
                    <?php endif; ?>
                    <a href="../../admin/php/manage-rooms.php" class="btn btn-outline">Manage Rooms</a>
                    <a href="service-requests.php" class="btn btn-outline">Service Requests</a>
                    <a href="logout.php" class="btn btn-outline">Logout</a>
                </div>
            </div>
        </header>

        <main class="admin-main">
            
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
            </div>

            
            <div class="dashboard-section">
                <div class="section-header">
                    <h2>Quick Actions</h2>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                    <?php if ($canBookRoom): ?>
                        <a href="../../admin/php/book-room.php" class="btn btn-primary" style="padding: 2rem; text-align: center; display: block;">
                            <h3 style="margin-bottom: 0.5rem;">📝</h3>
                            <strong>Book Room for Customer</strong>
                        </a>
                    <?php endif; ?>
                    <a href="../../admin/php/manage-rooms.php" class="btn btn-outline" style="padding: 2rem; text-align: center; display: block;">
                        <h3 style="margin-bottom: 0.5rem;">🏠</h3>
                        <strong>Manage Room Status</strong>
                    </a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>

