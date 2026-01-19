<?php
session_start();

require_once __DIR__ . '/../controllers/AdminAuthController.php';
require_once __DIR__ . '/../controllers/AdminBookingController.php';
AdminAuthController::requireLogin();

$bookingController = new AdminBookingController();
$dashboardUrl = 'dashboard.php';

require_once __DIR__ . '/../../config/database.php';

if (isset($_GET['ajax']) && $_GET['ajax'] === 'available_rooms') {
    header('Content-Type: application/json');
    $type = $_GET['room_type'] ?? '';
    $checkIn = $_GET['check_in_date'] ?? '';
    $checkOut = $_GET['check_out_date'] ?? '';
    
    try {
        $db = new Database();
        $conn = $db->getConnection();
        $sql = "SELECT id, room_number, floor_number FROM rooms 
                WHERE room_type = :type AND status = 'available'
                ORDER BY floor_number, room_number";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':type', $type);
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (Exception $e) {
        echo json_encode([]);
    }
    exit;
}

$errors = [];
$success = false;
$bookingData = null;
$availableRooms = [];

if (!empty($_GET['room_type'])) {
    $type = $_GET['room_type'];
    try {
        $db = new Database();
        $conn = $db->getConnection();
        $sql = "SELECT id, room_number, floor_number FROM rooms 
                WHERE room_type = :type AND status = 'available'
                ORDER BY floor_number, room_number";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':type', $type);
        $stmt->execute();
        $availableRooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $availableRooms = [];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'first_name' => $_POST['first_name'] ?? '',
        'last_name' => $_POST['last_name'] ?? '',
        'customer_email' => $_POST['customer_email'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'room_type' => $_POST['room_type'] ?? '',
        'room_id' => intval($_POST['room_id'] ?? 0),
        'check_in_date' => $_POST['check_in_date'] ?? '',
        'check_out_date' => $_POST['check_out_date'] ?? '',
        'number_of_guests' => intval($_POST['number_of_guests'] ?? 1)
    ];
    
    $result = $bookingController->processBooking($data);
    if ($result['success']) {
        $success = true;
        $bookingData = $result;
    } else {
        $errors = $result['errors'];
    }
}

$minDate = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Room</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <div class="header-content">
                <h1>Book Room</h1>
                <div class="header-actions">
                    <a href="<?php echo $dashboardUrl; ?>" class="btn btn-outline">Back to Dashboard</a>
                </div>
            </div>
        </header>

        <main class="admin-main">
            <?php if ($success): ?>
                <div class="success-card">
                    <h2>Booking Confirmed!</h2>
                    <p>Booking ID: #<?php echo $bookingData['booking_id']; ?></p>
                    <p>Room: <?php echo htmlspecialchars($bookingData['room_number']); ?></p>
                    <div class="form-actions">
                        <a href="book-room.php" class="btn btn-primary">Book Another</a>
                        <a href="<?php echo $dashboardUrl; ?>" class="btn btn-outline">Dashboard</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="booking-form-container">
                    <?php if (!empty($errors)): ?>
                        <div class="error-messages">
                            <?php foreach ($errors as $error): ?>
                                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="admin-booking-form">
                        <div class="form-section">
                            <h2>Customer Information</h2>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="first_name">First Name *</label>
                                    <input type="text" id="first_name" name="first_name" 
                                           value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="last_name">Last Name *</label>
                                    <input type="text" id="last_name" name="last_name" 
                                           value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="customer_email">Email *</label>
                                    <input type="email" id="customer_email" name="customer_email" 
                                           value="<?php echo htmlspecialchars($_POST['customer_email'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="phone">Phone *</label>
                                    <input type="tel" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h2>Booking Details</h2>
                            <div class="form-group">
                                <label for="room_type">Room Type *</label>
                                <select id="room_type" name="room_type" required onchange="loadRooms()">
                                    <option value="">Select Room Type</option>
                                    <option value="Deluxe Room" <?php echo (($_POST['room_type'] ?? '') === 'Deluxe Room') ? 'selected' : ''; ?>>Deluxe Room</option>
                                    <option value="Executive Suite" <?php echo (($_POST['room_type'] ?? '') === 'Executive Suite') ? 'selected' : ''; ?>>Executive Suite</option>
                                    <option value="Presidential Suite" <?php echo (($_POST['room_type'] ?? '') === 'Presidential Suite') ? 'selected' : ''; ?>>Presidential Suite</option>
                                    <option value="Romantic Suite" <?php echo (($_POST['room_type'] ?? '') === 'Romantic Suite') ? 'selected' : ''; ?>>Romantic Suite</option>
                                </select>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="check_in_date">Check-in Date *</label>
                                    <input type="date" id="check_in_date" name="check_in_date" 
                                           value="<?php echo htmlspecialchars($_POST['check_in_date'] ?? ''); ?>" 
                                           min="<?php echo $minDate; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="check_out_date">Check-out Date *</label>
                                    <input type="date" id="check_out_date" name="check_out_date" 
                                           value="<?php echo htmlspecialchars($_POST['check_out_date'] ?? ''); ?>" 
                                           min="<?php echo $minDate; ?>" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="room_id">Room Number *</label>
                                <select id="room_id" name="room_id" required>
                                    <option value="0">Select a room</option>
                                    <?php foreach ($availableRooms as $r): ?>
                                        <option value="<?php echo $r['id']; ?>" 
                                                <?php echo ((int)($_POST['room_id'] ?? 0) === (int)$r['id']) ? 'selected' : ''; ?>>
                                            Room <?php echo htmlspecialchars($r['room_number']); ?> (Floor <?php echo htmlspecialchars($r['floor_number'] ?? ''); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="number_of_guests">Number of Guests *</label>
                                <input type="number" id="number_of_guests" name="number_of_guests" 
                                       value="<?php echo htmlspecialchars($_POST['number_of_guests'] ?? '1'); ?>" 
                                       min="1" max="10" required>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary btn-large">Confirm Booking</button>
                            <a href="<?php echo $dashboardUrl; ?>" class="btn btn-outline">Cancel</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        function loadRooms() {
            const roomType = document.getElementById('room_type').value;
            const checkIn = document.getElementById('check_in_date').value;
            const checkOut = document.getElementById('check_out_date').value;
            const roomSelect = document.getElementById('room_id');
            
            if (!roomType) {
                roomSelect.innerHTML = '<option value="0">Select a room</option>';
                return;
            }
            
            roomSelect.innerHTML = '<option value="0">Loading...</option>';
            
            const url = `?ajax=available_rooms&room_type=${encodeURIComponent(roomType)}&check_in_date=${checkIn}&check_out_date=${checkOut}`;
            fetch(url)
                .then(r => r.json())
                .then(rooms => {
                    roomSelect.innerHTML = '<option value="0">Select a room</option>';
                    rooms.forEach(room => {
                        const opt = document.createElement('option');
                        opt.value = room.id;
                        opt.textContent = `Room ${room.room_number} (Floor ${room.floor_number || ''})`;
                        roomSelect.appendChild(opt);
                    });
                })
                .catch(() => {
                    roomSelect.innerHTML = '<option value="0">Failed to load rooms</option>';
                });
        }
        
        document.getElementById('check_in_date').addEventListener('change', loadRooms);
        document.getElementById('check_out_date').addEventListener('change', loadRooms);
    </script>
</body>
</html>
