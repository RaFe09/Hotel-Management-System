<?php

session_start();

 
$isAdmin = isset($_SESSION['admin_id']);
$isStaff = isset($_SESSION['staff_id']);

if ($isAdmin) {
    require_once __DIR__ . '/../controllers/AdminAuthController.php';
    require_once __DIR__ . '/../models/Room.php';
    AdminAuthController::requireLogin();
    
     
    class AdminRoomController {
        private $room;
        public function __construct() {
            $this->room = new Room();
        }
        public function getAllRooms() {
            return $this->room->getAll();
        }
        public function getRoomById($id) {
            return $this->room->getById($id);
        }
        public function updateRoomStatus($roomId, $status) {
            $validStatuses = ['available', 'booked', 'maintenance'];
            if (!in_array($status, $validStatuses)) {
                return ['success' => false, 'errors' => ['Invalid room status']];
            }
            if ($this->room->updateStatus($roomId, $status)) {
                return ['success' => true, 'message' => 'Room status updated successfully'];
            }
            return ['success' => false, 'errors' => ['Failed to update room status']];
        }
        public function getRoomStatistics() {
            return $this->room->getStatistics();
        }
    }
    
    $roomController = new AdminRoomController();
    $userType = 'admin';
    $userName = $_SESSION['admin_name'] ?? 'Admin';
    $dashboardUrl = 'dashboard.php';
} elseif ($isStaff) {
    require_once __DIR__ . '/../../staff/controllers/StaffAuthController.php';
    require_once __DIR__ . '/../../staff/controllers/StaffRoomController.php';
    StaffAuthController::requireLogin();
    $roomController = new StaffRoomController();
    $userType = 'staff';
    $userName = $_SESSION['staff_name'] ?? 'Staff';
    $dashboardUrl = '../../staff/php/dashboard.php';
} else {
     
    header("Location: ../../login_signup/php/login.php");
    exit();
}

$message = '';
$messageType = '';

 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $roomId = intval($_POST['room_id'] ?? 0);
    $newStatus = $_POST['new_status'] ?? '';
    
    if ($roomId > 0 && !empty($newStatus)) {
        $result = $roomController->updateRoomStatus($roomId, $newStatus);
        if ($result['success']) {
            $message = $result['message'];
            $messageType = 'success';
        } else {
            $message = !empty($result['errors']) ? implode(', ', $result['errors']) : 'Failed to update room status';
            $messageType = 'error';
        }
    }
}

 
$rooms = $roomController->getAllRooms();
$roomStats = $roomController->getRoomStatistics();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Rooms - <?php echo ucfirst($userType); ?></title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <div class="header-content">
                <h1>Manage Room Status</h1>
                <div class="header-actions">
                    <a href="<?php echo $dashboardUrl; ?>" class="btn btn-outline">← Back to Dashboard</a>
                </div>
            </div>
        </header>

        <main class="admin-main">
            <?php if ($message): ?>
                <div class="error-messages" style="<?php echo $messageType === 'success' ? 'background: #e8f5e9; border-color: #2e7d32;' : ''; ?>">
                    <div class="error-message" style="<?php echo $messageType === 'success' ? 'color: #2e7d32;' : ''; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                </div>
            <?php endif; ?>

            
            <div class="stats-grid" style="margin-bottom: 2rem;">
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
                    <div class="stat-icon available">🏨</div>
                    <div class="stat-content">
                        <h3><?php echo $roomStats['total']; ?></h3>
                        <p>Total Rooms</p>
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
                                <th>Current Status</th>
                                <th>Change Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($rooms)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">No rooms found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($rooms as $room): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($room['room_number']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($room['room_type']); ?></td>
                                        <td>Floor <?php echo $room['floor_number']; ?></td>
                                        <td>৳<?php echo number_format($room['price_per_night'], 2); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo htmlspecialchars($room['status']); ?>">
                                                <?php echo ucfirst($room['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to change the room status?');">
                                                <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
                                                <select name="new_status" class="room-status-select status-<?php echo htmlspecialchars($room['status']); ?>" required>
                                                    <option value="available" <?php echo $room['status'] === 'available' ? 'selected' : ''; ?>>Available</option>
                                                    <option value="booked" <?php echo $room['status'] === 'booked' ? 'selected' : ''; ?>>Booked</option>
                                                    <option value="maintenance" <?php echo $room['status'] === 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                                                </select>
                                                <button type="submit" name="update_status" class="btn btn-primary btn-small" style="margin-left: 5px;">Update</button>
                                            </form>
                                        </td>
                                        <td>
                                            <button onclick="changeStatus(<?php echo $room['id']; ?>, '<?php echo htmlspecialchars($room['status']); ?>')" class="btn btn-outline btn-small">Quick Change</button>
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

    <script>
        function changeStatus(roomId, currentStatus) {
            const statuses = ['available', 'booked', 'maintenance'];
            const statusLabels = {
                'available': 'Available',
                'booked': 'Booked',
                'maintenance': 'Maintenance'
            };
            
            let options = '';
            statuses.forEach(status => {
                const selected = status === currentStatus ? 'selected' : '';
                options += `<option value="${status}" ${selected}>${statusLabels[status]}</option>`;
            });
            
            const newStatus = prompt(`Select new status for Room ${roomId}:\n\n1. Available\n2. Booked\n3. Maintenance`, currentStatus);
            
            if (newStatus && newStatus !== currentStatus && statuses.includes(newStatus.toLowerCase())) {
                if (confirm(`Change room status from "${statusLabels[currentStatus]}" to "${statusLabels[newStatus.toLowerCase()]}"?`)) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `
                        <input type="hidden" name="room_id" value="${roomId}">
                        <input type="hidden" name="new_status" value="${newStatus.toLowerCase()}">
                        <input type="hidden" name="update_status" value="1">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            }
        }
    </script>
</body>
</html>

