<?php

session_start();
require_once __DIR__ . '/../controllers/AdminAuthController.php';
require_once __DIR__ . '/../controllers/AdminBookingController.php';

AdminAuthController::requireLogin();

$bookingController = new AdminBookingController();

// Get room type from URL parameter (optional)
$roomType = isset($_GET['room_type']) ? urldecode($_GET['room_type']) : '';
$validRoomTypes = ['Deluxe Room', 'Executive Suite', 'Presidential Suite', 'Romantic Suite'];
if (!empty($roomType) && !in_array($roomType, $validRoomTypes)) {
    $roomType = '';
}

// Get room details if room type is selected
$roomDetails = null;
if (!empty($roomType)) {
    $roomDetails = $bookingController->getRoomDetailsForBooking($roomType);
}

$errors = [];
$success = false;
$bookingData = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'room_type' => $_POST['room_type'] ?? '',
        'check_in_date' => $_POST['check_in_date'] ?? '',
        'check_out_date' => $_POST['check_out_date'] ?? '',
        'number_of_guests' => intval($_POST['number_of_guests'] ?? 1),
        'first_name' => $_POST['first_name'] ?? '',
        'last_name' => $_POST['last_name'] ?? '',
        'customer_email' => $_POST['customer_email'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'special_requests' => $_POST['special_requests'] ?? ''
    ];

    $result = $bookingController->processBooking($data);
    
    if ($result['success']) {
        $success = true;
        $bookingData = $result;
    } else {
        $errors = $result['errors'];
    }
}

// Set minimum date to today
$minDate = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Room for Customer - Admin</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <div class="header-content">
                <h1>Book Room for Customer</h1>
                <div class="header-actions">
                    <a href="dashboard.php" class="btn btn-outline">← Back to Dashboard</a>
                </div>
            </div>
        </header>

        <main class="admin-main">
            <?php if ($success): ?>
                <!-- Success Message -->
                <div class="success-card">
                    <div class="success-icon">✓</div>
                    <h2>Booking Confirmed!</h2>
                    <p>The room has been successfully booked for the customer.</p>
                    <div class="booking-summary">
                        <div class="summary-item">
                            <span class="summary-label">Booking ID:</span>
                            <span class="summary-value">#<?php echo $bookingData['booking_id']; ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Room Number:</span>
                            <span class="summary-value"><?php echo htmlspecialchars($bookingData['room_number']); ?></span>
                        </div>
                    </div>
                    <div class="success-actions">
                        <a href="book-room.php" class="btn btn-primary">Book Another Room</a>
                        <a href="dashboard.php" class="btn btn-outline">Back to Dashboard</a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Booking Form -->
                <div class="booking-form-container">
                    <form method="POST" class="admin-booking-form" id="bookingForm">
                        <!-- Customer Information -->
                        <div class="form-section">
                            <h2>Customer Information</h2>
                            <p class="section-note">If customer doesn't exist, a new account will be created automatically.</p>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="first_name">First Name *</label>
                                    <input type="text" id="first_name" name="first_name" 
                                           value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" 
                                           required>
                                </div>
                                <div class="form-group">
                                    <label for="last_name">Last Name *</label>
                                    <input type="text" id="last_name" name="last_name" 
                                           value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" 
                                           required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="customer_email">Email *</label>
                                    <input type="email" id="customer_email" name="customer_email" 
                                           value="<?php echo htmlspecialchars($_POST['customer_email'] ?? ''); ?>" 
                                           required>
                                </div>
                                <div class="form-group">
                                    <label for="phone">Phone *</label>
                                    <input type="tel" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" 
                                           required>
                                </div>
                            </div>
                        </div>

                        <!-- Booking Information -->
                        <div class="form-section">
                            <h2>Booking Information</h2>
                            
                            <?php if (!empty($errors)): ?>
                                <div class="error-messages">
                                    <?php foreach ($errors as $error): ?>
                                        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="form-group">
                                <label for="room_type">Room Type *</label>
                                <select id="room_type" name="room_type" required onchange="updateRoomDetails()">
                                    <option value="">Select Room Type</option>
                                    <option value="Deluxe Room" <?php echo ($roomType === 'Deluxe Room' || (isset($_POST['room_type']) && $_POST['room_type'] === 'Deluxe Room')) ? 'selected' : ''; ?>>Deluxe Room</option>
                                    <option value="Executive Suite" <?php echo ($roomType === 'Executive Suite' || (isset($_POST['room_type']) && $_POST['room_type'] === 'Executive Suite')) ? 'selected' : ''; ?>>Executive Suite</option>
                                    <option value="Presidential Suite" <?php echo ($roomType === 'Presidential Suite' || (isset($_POST['room_type']) && $_POST['room_type'] === 'Presidential Suite')) ? 'selected' : ''; ?>>Presidential Suite</option>
                                    <option value="Romantic Suite" <?php echo ($roomType === 'Romantic Suite' || (isset($_POST['room_type']) && $_POST['room_type'] === 'Romantic Suite')) ? 'selected' : ''; ?>>Romantic Suite</option>
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
                                <label for="number_of_guests">Number of Guests *</label>
                                <input type="number" id="number_of_guests" name="number_of_guests" 
                                       value="<?php echo htmlspecialchars($_POST['number_of_guests'] ?? '1'); ?>" 
                                       min="1" max="10" required>
                            </div>

                            <div class="form-group">
                                <label for="special_requests">Special Requests</label>
                                <textarea id="special_requests" name="special_requests" rows="4" 
                                          placeholder="Any special requests or preferences?"><?php echo htmlspecialchars($_POST['special_requests'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary btn-large">Confirm Booking</button>
                            <a href="dashboard.php" class="btn btn-outline">Cancel</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        function updateRoomDetails() {
            const roomType = document.getElementById('room_type').value;
            if (roomType) {
                // Could fetch room details via AJAX if needed
            }
        }
    </script>
</body>
</html>
