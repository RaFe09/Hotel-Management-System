<?php

session_start();

 
$isAdmin = isset($_SESSION['admin_id']);
$isStaff = isset($_SESSION['staff_id']);
require_once __DIR__ . '/../../config/database.php';

if ($isAdmin) {
    require_once __DIR__ . '/../controllers/AdminAuthController.php';
    require_once __DIR__ . '/../controllers/AdminBookingController.php';
    AdminAuthController::requireLogin();
    $bookingController = new AdminBookingController();
    $userType = 'admin';
    $userName = $_SESSION['admin_name'] ?? 'Admin';
    $dashboardUrl = 'dashboard.php';
} elseif ($isStaff) {
    require_once __DIR__ . '/../../staff/controllers/StaffAuthController.php';
    require_once __DIR__ . '/../../staff/controllers/StaffBookingController.php';
    StaffAuthController::requireLogin();
    $bookingController = new StaffBookingController();
    $userType = 'staff';
    $userName = $_SESSION['staff_name'] ?? 'Staff';
    $dashboardUrl = '../../staff/php/dashboard.php';

     
    $staffCanBook = false;
    $staffRole = '';
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
            $staffRole = $row['role'] ?? '';
            $staffRoleNorm = strtolower(trim((string)$staffRole));
            $staffCanBook = in_array($staffRoleNorm, ['receptionist', 'reception', 'frontdesk', 'front_desk', 'front desk'], true);
        } else {
            $staffCanBook = false;
        }
    } catch (Exception $e) {
        $staffCanBook = false;
    }

    if (!$staffCanBook) {
        http_response_code(403);
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Access Denied</title>
            <link rel="stylesheet" href="../css/styles.css">
        </head>
        <body>
            <div class="admin-container">
                <header class="admin-header">
                    <div class="header-content">
                        <h1>Access Denied</h1>
                        <div class="header-actions">
                            <a href="<?php echo $dashboardUrl; ?>" class="btn btn-outline">← Back to Dashboard</a>
                        </div>
                    </div>
                </header>
                <main class="admin-main">
                    <div class="dashboard-section">
                        <div class="section-header">
                            <h2>Only Receptionist Can Book Rooms</h2>
                            <p>Your staff role is: <strong><?php echo htmlspecialchars($staffRole ?: 'Not set'); ?></strong></p>
                            <p>Please contact admin to set your role to <code>receptionist</code> (run <code>db/new_features.sql</code> if the role column is missing).</p>
                        </div>
                    </div>
                </main>
            </div>
        </body>
        </html>
        <?php
        exit();
    }
} else {
     
    header("Location: ../../login_signup/php/login.php");
    exit();
}

 
if (isset($_GET['ajax']) && $_GET['ajax'] === 'available_rooms') {
    header('Content-Type: application/json; charset=UTF-8');
    $type = isset($_GET['room_type']) ? urldecode($_GET['room_type']) : '';
    $checkIn = $_GET['check_in_date'] ?? '';
    $checkOut = $_GET['check_out_date'] ?? '';

    $validRoomTypes = ['Deluxe Room', 'Executive Suite', 'Presidential Suite', 'Romantic Suite'];
    if (!in_array($type, $validRoomTypes, true)) {
        echo json_encode([]);
        exit;
    }

     
    $checkInOk = preg_match('/^\d{4}-\d{2}-\d{2}$/', $checkIn);
    $checkOutOk = preg_match('/^\d{4}-\d{2}-\d{2}$/', $checkOut);

    try {
        $db = new Database();
        $conn = $db->getConnection();

        if ($checkInOk && $checkOutOk && strtotime($checkOut) > strtotime($checkIn)) {
            $sql = "SELECT r.id, r.room_number, r.floor_number
                    FROM rooms r
                    WHERE r.room_type = :room_type
                      AND r.status = 'available'
                      AND r.id NOT IN (
                          SELECT b.room_id FROM bookings b
                          WHERE b.status IN ('pending', 'confirmed')
                            AND (b.check_in_date <= :check_out AND b.check_out_date >= :check_in)
                      )
                    ORDER BY r.floor_number, r.room_number";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':room_type', $type);
            $stmt->bindValue(':check_in', $checkIn);
            $stmt->bindValue(':check_out', $checkOut);
        } else {
            $sql = "SELECT r.id, r.room_number, r.floor_number
                    FROM rooms r
                    WHERE r.room_type = :room_type
                      AND r.status = 'available'
                    ORDER BY r.floor_number, r.room_number";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':room_type', $type);
        }
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($rows);
        exit;
    } catch (Exception $e) {
        echo json_encode([]);
        exit;
    }
}

 
$roomType = isset($_GET['room_type']) ? urldecode($_GET['room_type']) : '';
$validRoomTypes = ['Deluxe Room', 'Executive Suite', 'Presidential Suite', 'Romantic Suite'];
if (!empty($roomType) && !in_array($roomType, $validRoomTypes)) {
    $roomType = '';
}

 
$roomDetails = null;
if (!empty($roomType)) {
    $roomDetails = $bookingController->getRoomDetailsForBooking($roomType);
}

$errors = [];
$success = false;
$bookingData = null;
$availableRoomsForSelect = [];

 
$selectedRoomType = $_POST['room_type'] ?? $roomType;
$selectedCheckIn = $_POST['check_in_date'] ?? '';
$selectedCheckOut = $_POST['check_out_date'] ?? '';

if (!empty($selectedRoomType) && in_array($selectedRoomType, $validRoomTypes, true)) {
     
    $checkInOk = preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedCheckIn);
    $checkOutOk = preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedCheckOut);
    try {
        $db = new Database();
        $conn = $db->getConnection();
        if ($checkInOk && $checkOutOk && strtotime($selectedCheckOut) > strtotime($selectedCheckIn)) {
            $sql = "SELECT r.id, r.room_number, r.floor_number
                    FROM rooms r
                    WHERE r.room_type = :room_type
                      AND r.status = 'available'
                      AND r.id NOT IN (
                          SELECT b.room_id FROM bookings b
                          WHERE b.status IN ('pending', 'confirmed')
                            AND (b.check_in_date <= :check_out AND b.check_out_date >= :check_in)
                      )
                    ORDER BY r.floor_number, r.room_number";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':room_type', $selectedRoomType);
            $stmt->bindValue(':check_in', $selectedCheckIn);
            $stmt->bindValue(':check_out', $selectedCheckOut);
        } else {
            $sql = "SELECT r.id, r.room_number, r.floor_number
                    FROM rooms r
                    WHERE r.room_type = :room_type
                      AND r.status = 'available'
                    ORDER BY r.floor_number, r.room_number";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':room_type', $selectedRoomType);
        }
        $stmt->execute();
        $availableRoomsForSelect = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $availableRoomsForSelect = [];
    }
}

 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'room_type' => $_POST['room_type'] ?? '',
        'room_id' => intval($_POST['room_id'] ?? 0),
        'check_in_date' => $_POST['check_in_date'] ?? '',
        'check_out_date' => $_POST['check_out_date'] ?? '',
        'number_of_guests' => intval($_POST['number_of_guests'] ?? 1),
         
        'special_requests' => ($userType === 'admin') ? ($_POST['special_requests'] ?? '') : ''
    ];

     
    if ($_POST['customer_type'] === 'existing' && !empty($_POST['customer_id'])) {
        $data['customer_id'] = intval($_POST['customer_id']);
        $customer = $bookingController->getCustomerById($data['customer_id']);
        if (!$customer) {
            $errors[] = "Selected customer not found.";
        }
    } else {
        $data['first_name'] = $_POST['first_name'] ?? '';
        $data['last_name'] = $_POST['last_name'] ?? '';
        $data['customer_email'] = $_POST['customer_email'] ?? '';
        $data['phone'] = $_POST['phone'] ?? '';
    }

    if (empty($errors)) {
        $result = $bookingController->processBooking($data);
        if ($result['success']) {
            $success = true;
            $bookingData = $result;
        } else {
            $errors = $result['errors'];
        }
    }
}

 
$minDate = date('Y-m-d');

 
$searchUrl = 'search-customers.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Room for Customer - <?php echo ucfirst($userType); ?></title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <div class="header-content">
                <h1>Book Room for Customer</h1>
                <div class="header-actions">
                    <a href="<?php echo $dashboardUrl; ?>" class="btn btn-outline">← Back to Dashboard</a>
                </div>
            </div>
        </header>

        <main class="admin-main">
            <?php if ($success): ?>
                
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
                        <a href="<?php echo $dashboardUrl; ?>" class="btn btn-outline">Back to Dashboard</a>
                    </div>
                </div>
            <?php else: ?>
                
                <div class="booking-form-container">
                    <form method="POST" class="admin-booking-form" id="bookingForm">
                        
                        <div class="form-section">
                            <h2>Customer Information</h2>
                            
                            
                            <div class="form-group">
                                <label>Customer Type *</label>
                                <div class="radio-group">
                                    <label class="radio-label">
                                        <input type="radio" name="customer_type" value="existing" id="customer_type_existing" 
                                               <?php echo (isset($_POST['customer_type']) && $_POST['customer_type'] === 'existing') ? 'checked' : ''; ?> 
                                               onchange="toggleCustomerForm()">
                                        <span>Existing Customer</span>
                                    </label>
                                    <label class="radio-label">
                                        <input type="radio" name="customer_type" value="new" id="customer_type_new" 
                                               <?php echo (!isset($_POST['customer_type']) || $_POST['customer_type'] === 'new') ? 'checked' : ''; ?> 
                                               onchange="toggleCustomerForm()">
                                        <span>New Customer</span>
                                    </label>
                                </div>
                            </div>

                            
                            <div id="existing_customer_section" style="display: none;">
                                <div class="form-group">
                                    <label for="customer_search">Search Customer *</label>
                                    <input type="text" id="customer_search" name="customer_search" 
                                           placeholder="Search by name, email, or phone..." 
                                           autocomplete="off"
                                           oninput="searchCustomers(this.value)">
                                    <input type="hidden" id="customer_id" name="customer_id" value="<?php echo htmlspecialchars($_POST['customer_id'] ?? ''); ?>">
                                    <div id="customer_search_results" style="position: relative;"></div>
                                </div>
                                <div id="selected_customer_info" class="selected-customer-info" style="display: none;">
                                    <strong>Selected Customer:</strong>
                                    <div id="selected_customer_details"></div>
                                    <button type="button" onclick="clearCustomerSelection()" class="btn-change">Change Customer</button>
                                </div>
                            </div>

                            
                            <div id="new_customer_section">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="first_name">First Name *</label>
                                        <input type="text" id="first_name" name="first_name" 
                                               value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="last_name">Last Name *</label>
                                        <input type="text" id="last_name" name="last_name" 
                                               value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>">
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="customer_email">Email *</label>
                                        <input type="email" id="customer_email" name="customer_email" 
                                               value="<?php echo htmlspecialchars($_POST['customer_email'] ?? ''); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="phone">Phone *</label>
                                        <input type="tel" id="phone" name="phone" 
                                               value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        
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
                                <label for="room_id">Room Number *</label>
                                <select id="room_id" name="room_id" required>
                                    <option value="0">Select a room number</option>
                                    <?php foreach ($availableRoomsForSelect as $r): ?>
                                        <option value="<?php echo (int)$r['id']; ?>" <?php echo ((int)($_POST['room_id'] ?? 0) === (int)$r['id']) ? 'selected' : ''; ?>>
                                            Room <?php echo htmlspecialchars($r['room_number']); ?> (Floor <?php echo htmlspecialchars($r['floor_number'] ?? ''); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small style="color:#777;">Room list updates based on Room Type + Dates.</small>
                            </div>

                            <div class="form-group">
                                <label for="number_of_guests">Number of Guests *</label>
                                <input type="number" id="number_of_guests" name="number_of_guests" 
                                       value="<?php echo htmlspecialchars($_POST['number_of_guests'] ?? '1'); ?>" 
                                       min="1" max="10" required>
                            </div>

                            <div class="form-group">
                                <?php if ($userType === 'admin'): ?>
                                    <label for="special_requests">Special Requests</label>
                                    <textarea id="special_requests" name="special_requests" rows="4" 
                                              placeholder="Any special requests or preferences?"><?php echo htmlspecialchars($_POST['special_requests'] ?? ''); ?></textarea>
                                <?php endif; ?>
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

    <style>
        .radio-group {
            display: flex;
            gap: 20px;
            margin-top: 10px;
        }
        .radio-label {
            display: flex;
            align-items: center;
            cursor: pointer;
        }
        .radio-label input {
            margin-right: 8px;
        }
        .customer-search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-top: 5px;
            max-height: 300px;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .customer-result-item {
            padding: 12px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }
        .customer-result-item:hover {
            background: #f5f5f5;
        }
        .selected-customer-info {
            padding: 15px;
            background: #f0f0f0;
            border-radius: 5px;
            margin-top: 10px;
        }
        .btn-change {
            margin-top: 10px;
            padding: 5px 10px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
    </style>

    <script>
        let searchTimeout;
        const searchUrl = '<?php echo $searchUrl; ?>';

        function toggleCustomerForm() {
            const isExisting = document.getElementById('customer_type_existing').checked;
            const existingSection = document.getElementById('existing_customer_section');
            const newSection = document.getElementById('new_customer_section');
            const newFields = ['first_name', 'last_name', 'customer_email', 'phone'];
            
            existingSection.style.display = isExisting ? 'block' : 'none';
            newSection.style.display = isExisting ? 'none' : 'block';
            
            newFields.forEach(field => {
                document.getElementById(field).required = !isExisting;
            });
            
            if (!isExisting) {
                clearCustomerSelection();
            }
        }

        function searchCustomers(term) {
            clearTimeout(searchTimeout);
            const resultsDiv = document.getElementById('customer_search_results');
            
            if (term.length < 2) {
                resultsDiv.innerHTML = '';
                return;
            }

            searchTimeout = setTimeout(() => {
                fetch(`${searchUrl}?q=${encodeURIComponent(term)}`)
                    .then(r => r.json())
                    .then(customers => showResults(customers))
                    .catch(() => resultsDiv.innerHTML = '');
            }, 300);
        }

        function showResults(customers) {
            const resultsDiv = document.getElementById('customer_search_results');
            
            if (customers.length === 0) {
                resultsDiv.innerHTML = '<div class="customer-result-item">No customers found</div>';
                return;
            }

            let html = '<div class="customer-search-results">';
            customers.forEach(c => {
                const data = JSON.stringify(c).replace(/"/g, '&quot;');
                html += `<div class="customer-result-item" data-customer='${data}'>
                    <strong>${escapeHtml(c.first_name)} ${escapeHtml(c.last_name)}</strong><br>
                    <small style="color: #666;">${escapeHtml(c.email)} | ${escapeHtml(c.phone)}</small>
                </div>`;
            });
            html += '</div>';
            resultsDiv.innerHTML = html;
            
            
            resultsDiv.querySelectorAll('.customer-result-item').forEach(item => {
                item.addEventListener('click', function() {
                    const c = JSON.parse(this.getAttribute('data-customer'));
                    selectCustomer(c.id, c.first_name, c.last_name, c.email, c.phone);
                });
            });
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function selectCustomer(id, firstName, lastName, email, phone) {
            document.getElementById('customer_id').value = id;
            document.getElementById('customer_search').value = firstName + ' ' + lastName;
            document.getElementById('customer_search_results').innerHTML = '';
            
            const details = document.getElementById('selected_customer_details');
            details.innerHTML = '<div style="margin-top: 5px;">' +
                '<strong>' + escapeHtml(firstName) + ' ' + escapeHtml(lastName) + '</strong><br>' +
                'Email: ' + escapeHtml(email) + '<br>' +
                'Phone: ' + escapeHtml(phone) +
                '</div>';
            document.getElementById('selected_customer_info').style.display = 'block';
        }

        function clearCustomerSelection() {
            document.getElementById('customer_id').value = '';
            document.getElementById('customer_search').value = '';
            document.getElementById('customer_search_results').innerHTML = '';
            document.getElementById('selected_customer_info').style.display = 'none';
        }

        function updateRoomDetails() {
            updateAvailableRooms();
        }

        function updateAvailableRooms() {
            const roomType = document.getElementById('room_type')?.value || '';
            const checkIn = document.getElementById('check_in_date')?.value || '';
            const checkOut = document.getElementById('check_out_date')?.value || '';
            const roomSelect = document.getElementById('room_id');
            if (!roomSelect) return;

            
            roomSelect.innerHTML = '<option value="0">Select a room number</option>';

            if (!roomType) return;

            const url = new URL(window.location.href);
            url.searchParams.set('ajax', 'available_rooms');
            url.searchParams.set('room_type', roomType);
            url.searchParams.set('check_in_date', checkIn);
            url.searchParams.set('check_out_date', checkOut);

            fetch(url.toString())
                .then(r => r.json())
                .then(rows => {
                    if (!Array.isArray(rows) || rows.length === 0) {
                        const opt = document.createElement('option');
                        opt.value = '0';
                        opt.disabled = true;
                        opt.textContent = 'No rooms available';
                        roomSelect.appendChild(opt);
                        return;
                    }
                    rows.forEach(row => {
                        const opt = document.createElement('option');
                        opt.value = row.id;
                        opt.textContent = `Room ${row.room_number} (Floor ${row.floor_number ?? ''})`;
                        roomSelect.appendChild(opt);
                    });
                })
                .catch(() => {
                    const opt = document.createElement('option');
                    opt.value = '0';
                    opt.disabled = true;
                    opt.textContent = 'Failed to load rooms';
                    roomSelect.appendChild(opt);
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            toggleCustomerForm();
            updateAvailableRooms();

            
            ['room_type','check_in_date','check_out_date'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.addEventListener('change', updateAvailableRooms);
            });
            
            
            document.addEventListener('click', function(e) {
                const results = document.getElementById('customer_search_results');
                const input = document.getElementById('customer_search');
                if (!results.contains(e.target) && e.target !== input) {
                    results.innerHTML = '';
                }
            });

            
            document.getElementById('bookingForm').addEventListener('submit', function(e) {
                const isExisting = document.getElementById('customer_type_existing').checked;
                
                if (isExisting) {
                    if (!document.getElementById('customer_id').value) {
                        e.preventDefault();
                        alert('Please select a customer');
                        return false;
                    }
                } else {
                    const fields = ['first_name', 'last_name', 'customer_email', 'phone'];
                    for (let field of fields) {
                        if (!document.getElementById(field).value.trim()) {
                            e.preventDefault();
                            alert('Please fill in all customer fields');
                            return false;
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>
