<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login_signup/php/login.php?redirect=booking');
    exit;
}

require_once __DIR__ . '/../controllers/BookingController.php';
require_once __DIR__ . '/../controllers/RoomController.php';

$bookingController = new BookingController();
$roomController = new RoomController();

// Get room type from URL parameter
$roomType = isset($_GET['room_type']) ? urldecode($_GET['room_type']) : '';

// Validate room type
$validRoomTypes = ['Deluxe Room', 'Executive Suite', 'Presidential Suite', 'Romantic Suite'];
if (!in_array($roomType, $validRoomTypes)) {
    header('Location: rooms.php');
    exit;
}

// Get room details
$roomDetails = $bookingController->getRoomDetailsForBooking($roomType);
if (!$roomDetails) {
    header('Location: rooms.php');
    exit;
}

$errors = [];
$success = false;
$bookingData = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'customer_id' => $_SESSION['user_id'],
        'room_type' => $roomType,
        'check_in_date' => $_POST['check_in_date'] ?? '',
        'check_out_date' => $_POST['check_out_date'] ?? '',
        'number_of_guests' => intval($_POST['number_of_guests'] ?? 1),
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

// Function to get room image
function getRoomImage($roomType) {
    $imageMap = [
        'Deluxe Room' => 'Deluxe Room.png',
        'Executive Suite' => 'Executive Suite.jpg',
        'Presidential Suite' => 'Presidential Suite.jpg',
        'Romantic Suite' => 'Romantic Suite.jpg'
    ];
    
    $imageName = isset($imageMap[$roomType]) ? $imageMap[$roomType] : 'Deluxe Room.png';
    return '../../image/' . $imageName;
}

// Set minimum date to today
$minDate = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Room - Grand Hotel</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="rooms-container">
        <header class="rooms-header">
            <h1>Grand Hotel</h1>
            <h2>Book Your Stay</h2>
            <div class="header-actions">
                <a href="room-details.php?type=<?php echo urlencode($roomType); ?>" class="btn-back">← Back to Room Details</a>
            </div>
        </header>

        <?php if ($success): ?>
            <!-- Success Message -->
            <div class="booking-success">
                <div class="success-icon">✓</div>
                <h2>Booking Confirmed!</h2>
                <p>Your reservation has been successfully created.</p>
                <div class="booking-summary">
                    <div class="summary-item">
                        <span class="summary-label">Booking ID:</span>
                        <span class="summary-value">#<?php echo $bookingData['booking_id']; ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Room Number:</span>
                        <span class="summary-value"><?php echo htmlspecialchars($bookingData['room_number']); ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Room Type:</span>
                        <span class="summary-value"><?php echo htmlspecialchars($roomType); ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Check-in:</span>
                        <span class="summary-value"><?php echo date('F d, Y', strtotime($_POST['check_in_date'])); ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Check-out:</span>
                        <span class="summary-value"><?php echo date('F d, Y', strtotime($_POST['check_out_date'])); ?></span>
                    </div>
                </div>
                <div class="success-actions">
                    <a href="rooms.php" class="btn btn-primary">View All Rooms</a>
                    <a href="../../landing/php/index.php" class="btn btn-outline">Back to Home</a>
                </div>
            </div>
        <?php else: ?>
            <!-- Booking Form -->
            <div class="booking-page">
                <div class="booking-content">
                    <div class="booking-form-section">
                        <h2>Booking Information</h2>
                        
                        <?php if (!empty($errors)): ?>
                            <div class="error-messages">
                                <?php foreach ($errors as $error): ?>
                                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="booking-form" id="bookingForm">
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

                            <button type="submit" class="btn btn-primary btn-submit">Confirm Booking</button>
                        </form>
                    </div>

                    <div class="booking-summary-section">
                        <div class="room-card-booking">
                            <div class="room-image-booking">
                                <img src="<?php echo getRoomImage($roomType); ?>" alt="<?php echo htmlspecialchars($roomType); ?>">
                            </div>
                            <div class="room-info-booking">
                                <h3><?php echo htmlspecialchars($roomType); ?></h3>
                                <div class="room-price-booking">
                                    <span class="price-amount">৳<?php echo number_format($roomDetails['price_per_night'], 2); ?></span>
                                    <span class="price-period">per night</span>
                                </div>
                                <div class="room-availability">
                                    <?php if ($roomDetails['available_count'] > 0): ?>
                                        <span class="availability-badge available"><?php echo $roomDetails['available_count']; ?> rooms available</span>
                                    <?php else: ?>
                                        <span class="availability-badge unavailable">Currently unavailable</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="price-breakdown" id="priceBreakdown">
                            <h4>Price Breakdown</h4>
                            <div class="breakdown-item">
                                <span>Price per night:</span>
                                <span>৳<?php echo number_format($roomDetails['price_per_night'], 2); ?></span>
                            </div>
                            <div class="breakdown-item">
                                <span>Number of nights:</span>
                                <span id="nightsCount">-</span>
                            </div>
                            <div class="breakdown-total">
                                <span>Total Price:</span>
                                <span id="totalPrice">৳0.00</span>
                            </div>
                        </div>

                        <div class="booking-note">
                            <p><strong>Note:</strong> Your booking will be confirmed immediately upon submission. You will receive a confirmation email shortly.</p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Calculate price breakdown
        const checkInInput = document.getElementById('check_in_date');
        const checkOutInput = document.getElementById('check_out_date');
        const pricePerNight = <?php echo $roomDetails['price_per_night']; ?>;
        
        function calculatePrice() {
            const checkIn = checkInInput.value;
            const checkOut = checkOutInput.value;
            
            if (checkIn && checkOut) {
                const checkInDate = new Date(checkIn);
                const checkOutDate = new Date(checkOut);
                
                if (checkOutDate > checkInDate) {
                    const diffTime = Math.abs(checkOutDate - checkInDate);
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                    const totalPrice = pricePerNight * diffDays;
                    
                    document.getElementById('nightsCount').textContent = diffDays + ' night' + (diffDays !== 1 ? 's' : '');
                    document.getElementById('totalPrice').textContent = '৳' + totalPrice.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                } else {
                    document.getElementById('nightsCount').textContent = '-';
                    document.getElementById('totalPrice').textContent = '৳0.00';
                }
            } else {
                document.getElementById('nightsCount').textContent = '-';
                document.getElementById('totalPrice').textContent = '৳0.00';
            }
        }
        
        if (checkInInput && checkOutInput) {
            checkInInput.addEventListener('change', calculatePrice);
            checkOutInput.addEventListener('change', calculatePrice);
        }
    </script>
</body>
</html>

