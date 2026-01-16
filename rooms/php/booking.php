<?php
session_start();

 
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login_signup/php/login.php?redirect=booking');
    exit;
}

require_once __DIR__ . '/../controllers/BookingController.php';
require_once __DIR__ . '/../controllers/RoomController.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../utils/mailer.php';

$bookingController = new BookingController();
$roomController = new RoomController();

 
$roomType = isset($_GET['room_type']) ? urldecode($_GET['room_type']) : '';

 
$validRoomTypes = ['Deluxe Room', 'Executive Suite', 'Presidential Suite', 'Romantic Suite'];
if (!in_array($roomType, $validRoomTypes)) {
    header('Location: rooms.php');
    exit;
}

 
$roomDetails = $bookingController->getRoomDetailsForBooking($roomType);
if (!$roomDetails) {
    header('Location: rooms.php');
    exit;
}

$errors = [];
$success = false;
$bookingData = null;
$extras = [
    'breakfast' => 0,
    'parking' => 0,
    'airport_pickup' => 0,
    'extra_total' => 0
];

 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'customer_id' => $_SESSION['user_id'],
        'room_type' => $roomType,
        'check_in_date' => $_POST['check_in_date'] ?? '',
        'check_out_date' => $_POST['check_out_date'] ?? '',
        'number_of_guests' => intval($_POST['number_of_guests'] ?? 1),
        'special_requests' => $_POST['special_requests'] ?? ''
    ];

     
    $priceBreakfast = 500;
    $priceParking = 300;
    $priceAirport = 1500;
    $extras['breakfast'] = isset($_POST['breakfast']) ? 1 : 0;
    $extras['parking'] = isset($_POST['parking']) ? 1 : 0;
    $extras['airport_pickup'] = isset($_POST['airport_pickup']) ? 1 : 0;
    $extras['extra_total'] = ($extras['breakfast'] ? $priceBreakfast : 0) +
                             ($extras['parking'] ? $priceParking : 0) +
                             ($extras['airport_pickup'] ? $priceAirport : 0);

    $result = $bookingController->processBooking($data);
    
    if ($result['success']) {
        $success = true;
        $bookingData = $result;

         
        try {
            $db = new Database();
            $conn = $db->getConnection();
            $check = $conn->prepare("SHOW TABLES LIKE 'booking_services'");
            $check->execute();
            if ($check->rowCount() > 0) {
                $sql = "INSERT INTO booking_services (booking_id, breakfast, parking, airport_pickup, extra_total)
                        VALUES (:booking_id, :breakfast, :parking, :airport_pickup, :extra_total)
                        ON DUPLICATE KEY UPDATE
                            breakfast = VALUES(breakfast),
                            parking = VALUES(parking),
                            airport_pickup = VALUES(airport_pickup),
                            extra_total = VALUES(extra_total),
                            updated_at = NOW()";
                $stmt = $conn->prepare($sql);
                $stmt->bindValue(':booking_id', $bookingData['booking_id']);
                $stmt->bindValue(':breakfast', $extras['breakfast']);
                $stmt->bindValue(':parking', $extras['parking']);
                $stmt->bindValue(':airport_pickup', $extras['airport_pickup']);
                $stmt->bindValue(':extra_total', $extras['extra_total']);
                $stmt->execute();
            }
        } catch (Exception $e) {
             
        }

         
        $to = $_SESSION['user_email'] ?? '';
        $subject = "Grand Hotel Booking Confirmation #" . $bookingData['booking_id'];
        $msg = "<h2>Booking Confirmed!</h2>
                <p>Thanks for booking with Grand Hotel.</p>
                <p><strong>Booking ID:</strong> #" . htmlspecialchars($bookingData['booking_id']) . "</p>
                <p><strong>Room:</strong> " . htmlspecialchars($roomType) . " (Room " . htmlspecialchars($bookingData['room_number']) . ")</p>
                <p><strong>Check-in:</strong> " . htmlspecialchars($data['check_in_date']) . "</p>
                <p><strong>Check-out:</strong> " . htmlspecialchars($data['check_out_date']) . "</p>
                <p><strong>Extras:</strong> Breakfast(" . ($extras['breakfast'] ? 'Yes' : 'No') . "),
                    Parking(" . ($extras['parking'] ? 'Yes' : 'No') . "),
                    Airport Pickup(" . ($extras['airport_pickup'] ? 'Yes' : 'No') . ")</p>
                <p><strong>Extra Total:</strong> ৳" . number_format($extras['extra_total'], 2) . "</p>";
        send_simple_email($to, $subject, $msg);
    } else {
        $errors = $result['errors'];
    }
}

 
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
                    <a href="my-bookings.php" class="btn btn-outline">My Bookings</a>
                    <a href="../../landing/php/index.php" class="btn btn-outline">Back to Home</a>
                </div>
            </div>
        <?php else: ?>
            
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

                            <div class="form-group">
                                <label>Additional Services</label>
                                <div style="display:flex; flex-direction:column; gap:8px;">
                                    <label style="display:flex; gap:8px; align-items:center;">
                                        <input type="checkbox" name="breakfast" <?php echo isset($_POST['breakfast']) ? 'checked' : ''; ?>>
                                        Breakfast (+৳500)
                                    </label>
                                    <label style="display:flex; gap:8px; align-items:center;">
                                        <input type="checkbox" name="parking" <?php echo isset($_POST['parking']) ? 'checked' : ''; ?>>
                                        Parking (+৳300)
                                    </label>
                                    <label style="display:flex; gap:8px; align-items:center;">
                                        <input type="checkbox" name="airport_pickup" <?php echo isset($_POST['airport_pickup']) ? 'checked' : ''; ?>>
                                        Airport Pickup (+৳1500)
                                    </label>
                                </div>
                                <small style="color:#777;">These are simple fixed prices (educational).</small>
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
                            <div class="breakdown-item">
                                <span>Extras:</span>
                                <span id="extraTotal">৳0.00</span>
                            </div>
                            <div class="breakdown-total">
                                <span>Grand Total:</span>
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
        
        const checkInInput = document.getElementById('check_in_date');
        const checkOutInput = document.getElementById('check_out_date');
        const pricePerNight = <?php echo $roomDetails['price_per_night']; ?>;
        const breakfastCb = document.querySelector('input[name="breakfast"]');
        const parkingCb = document.querySelector('input[name="parking"]');
        const airportCb = document.querySelector('input[name="airport_pickup"]');
        const PRICE_BREAKFAST = 500;
        const PRICE_PARKING = 300;
        const PRICE_AIRPORT = 1500;
        
        function calculatePrice() {
            const checkIn = checkInInput.value;
            const checkOut = checkOutInput.value;
            const extras = (breakfastCb && breakfastCb.checked ? PRICE_BREAKFAST : 0) +
                           (parkingCb && parkingCb.checked ? PRICE_PARKING : 0) +
                           (airportCb && airportCb.checked ? PRICE_AIRPORT : 0);
            
            if (checkIn && checkOut) {
                const checkInDate = new Date(checkIn);
                const checkOutDate = new Date(checkOut);
                
                if (checkOutDate > checkInDate) {
                    const diffTime = Math.abs(checkOutDate - checkInDate);
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                    const totalPrice = (pricePerNight * diffDays) + extras;
                    
                    document.getElementById('nightsCount').textContent = diffDays + ' night' + (diffDays !== 1 ? 's' : '');
                    document.getElementById('extraTotal').textContent = '৳' + extras.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                    document.getElementById('totalPrice').textContent = '৳' + totalPrice.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                } else {
                    document.getElementById('nightsCount').textContent = '-';
                    document.getElementById('extraTotal').textContent = '৳' + extras.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                    document.getElementById('totalPrice').textContent = '৳0.00';
                }
            } else {
                document.getElementById('nightsCount').textContent = '-';
                document.getElementById('extraTotal').textContent = '৳' + extras.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                document.getElementById('totalPrice').textContent = '৳0.00';
            }
        }
        
        if (checkInInput && checkOutInput) {
            checkInInput.addEventListener('change', calculatePrice);
            checkOutInput.addEventListener('change', calculatePrice);
        }
        if (breakfastCb) breakfastCb.addEventListener('change', calculatePrice);
        if (parkingCb) parkingCb.addEventListener('change', calculatePrice);
        if (airportCb) airportCb.addEventListener('change', calculatePrice);

        
        calculatePrice();
    </script>
</body>
</html>

