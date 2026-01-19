<?php
session_start();

require_once __DIR__ . '/../controllers/AdminAuthController.php';
require_once __DIR__ . '/../models/Room.php';
AdminAuthController::requireLogin();

$room = new Room();
$dashboardUrl = 'dashboard.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $roomId = intval($_POST['room_id'] ?? 0);
    $newStatus = $_POST['new_status'] ?? '';
    
    if ($roomId > 0 && !empty($newStatus)) {
        if ($room->updateStatus($roomId, $newStatus)) {
            $message = "Room status updated successfully";
            $messageType = 'success';
        } else {
            $message = "Failed to update room status";
            $messageType = 'error';
        }
    }
}

$rooms = $room->getAll();
$roomStats = $room->getStatistics();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Rooms</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <div class="header-content">
                <h1>Manage Rooms</h1>
                <div class="header-actions">
                    <a href="<?php echo $dashboardUrl; ?>" class="btn btn-outline">Back to Dashboard</a>
                </div>
            </div>
        </header>

        <main class="admin-main">
            <?php if ($message): ?>
                <div style="background: <?php echo $messageType === 'success' ? '#d4edda' : '#f8d7da'; ?>; 
                           color: <?php echo $messageType === 'success' ? '#155724' : '#721c24'; ?>; 
                           padding: 15px; margin: 20px 0; border-radius: 5px;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-content">
                        <h3><?php echo $roomStats['total']; ?></h3>
                        <p>Total Rooms</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-content">
                        <h3><?php echo $roomStats['available']; ?></h3>
                        <p>Available</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-content">
                        <h3><?php echo $roomStats['booked']; ?></h3>
                        <p>Booked</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-content">
                        <h3><?php echo $roomStats['maintenance']; ?></h3>
                        <p>Maintenance</p>
                    </div>
                </div>
            </div>

            <div class="dashboard-section">
                <div class="section-header">
                    <h2>All Rooms</h2>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Room Number</th>
                                <th>Room Type</th>
                                <th>Floor</th>
                                <th>Price/Night</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($rooms)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No rooms found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($rooms as $r): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($r['room_number']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($r['room_type']); ?></td>
                                        <td>Floor <?php echo $r['floor_number']; ?></td>
                                        <td>৳<?php echo number_format($r['price_per_night'], 2); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo htmlspecialchars($r['status']); ?>">
                                                <?php echo ucfirst($r['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="room_id" value="<?php echo $r['id']; ?>">
                                                <select name="new_status" style="padding:5px; margin-right:5px;">
                                                    <option value="available" <?php echo $r['status'] === 'available' ? 'selected' : ''; ?>>Available</option>
                                                    <option value="booked" <?php echo $r['status'] === 'booked' ? 'selected' : ''; ?>>Booked</option>
                                                    <option value="maintenance" <?php echo $r['status'] === 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                                                </select>
                                                <button type="submit" name="update_status" class="btn btn-primary btn-small">Update</button>
                                            </form>
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
