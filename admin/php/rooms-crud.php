<?php
session_start();
require_once __DIR__ . '/../controllers/AdminAuthController.php';
require_once __DIR__ . '/../models/Room.php';

AdminAuthController::requireLogin();

$roomModel = new Room();

$message = '';
$error = '';

function simpleValidateRoom($data) {
    $errors = [];

    if (empty(trim($data['room_number'] ?? ''))) {
        $errors[] = "Room number is required";
    }
    if (empty(trim($data['room_type'] ?? ''))) {
        $errors[] = "Room type is required";
    }
    if (empty($data['floor_number']) || intval($data['floor_number']) <= 0) {
        $errors[] = "Floor number must be greater than 0";
    }
    if (!isset($data['price_per_night']) || floatval($data['price_per_night']) <= 0) {
        $errors[] = "Price per night must be greater than 0";
    }

    $validStatuses = ['available', 'booked', 'maintenance'];
    $status = $data['status'] ?? 'available';
    if (!in_array($status, $validStatuses)) {
        $errors[] = "Invalid status";
    }

    return $errors;
}

 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $errors = simpleValidateRoom($_POST);
    if (!empty($errors)) {
        $error = implode(', ', $errors);
    } else {
        if ($roomModel->create($_POST)) {
            $message = "Room added successfully";
        } else {
            $error = "Failed to add room (maybe duplicate room number?)";
        }
    }
}

 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    $roomId = intval($_POST['room_id'] ?? 0);
    if ($roomId <= 0) {
        $error = "Invalid room id";
    } else {
        $errors = simpleValidateRoom($_POST);
        if (!empty($errors)) {
            $error = implode(', ', $errors);
        } else {
            if ($roomModel->update($roomId, $_POST)) {
                $message = "Room updated successfully";
            } else {
                $error = "Failed to update room";
            }
        }
    }
}

 
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $roomId = intval($_GET['delete']);
    if ($roomId > 0) {
        if ($roomModel->delete($roomId)) {
            $message = "Room deleted successfully";
        } else {
            $error = "Failed to delete room (maybe it has bookings?)";
        }
    }
}

$rooms = $roomModel->getAll();
$typesFromDb = $roomModel->getDistinctTypes();

 
$suggestedTypes = ['Single', 'Double', 'Suite', 'Deluxe', 'Deluxe Room', 'Executive Suite', 'Presidential Suite', 'Romantic Suite'];
$roomTypes = array_values(array_unique(array_merge($suggestedTypes, $typesFromDb)));
sort($roomTypes);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Listings (CRUD) - Admin</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .mini-form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 10px; }
        .mini-form-grid .form-group { margin-bottom: 0; }
        .small-input { padding: 10px; border: 1px solid #ddd; border-radius: 6px; width: 100%; }
        .desc-input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; }
    </style>
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <div class="header-content">
                <h1>Room Listings (Add / Update / Delete)</h1>
                <div class="header-actions">
                    <span class="admin-name">Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                    <a href="dashboard.php" class="btn btn-outline">Dashboard</a>
                    <a href="manage-rooms.php" class="btn btn-outline">Room Status</a>
                    <a href="logout.php" class="btn btn-outline">Logout</a>
                </div>
            </div>
        </header>

        <main class="admin-main">
            <?php if ($message): ?>
                <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 15px; margin: 20px 0; border-radius: 5px;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error" style="background: #f8d7da; color: #721c24; padding: 15px; margin: 20px 0; border-radius: 5px;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="dashboard-section">
                <div class="section-header">
                    <h2>Add New Room</h2>
                    <p>Simple form (student style). This will create a new row in the <code>rooms</code> table.</p>
                </div>

                <form method="POST" style="padding: 15px; background: white; border-radius: 8px; border: 1px solid #eee;">
                    <input type="hidden" name="action" value="create">

                    <div class="mini-form-grid">
                        <div class="form-group">
                            <label>Room Number *</label>
                            <input class="small-input" type="text" name="room_number" placeholder="e.g. 101" required>
                        </div>
                        <div class="form-group">
                            <label>Room Type *</label>
                            <select class="small-input" name="room_type" required>
                                <option value="">Select type</option>
                                <?php foreach ($roomTypes as $t): ?>
                                    <option value="<?php echo htmlspecialchars($t); ?>"><?php echo htmlspecialchars($t); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small style="color:#777;">If you want a custom type, just type it when editing (below).</small>
                        </div>
                        <div class="form-group">
                            <label>Status *</label>
                            <select class="small-input" name="status" required>
                                <option value="available">Available</option>
                                <option value="booked">Booked</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Floor *</label>
                            <input class="small-input" type="number" name="floor_number" min="1" value="1" required>
                        </div>
                        <div class="form-group">
                            <label>Price per Night *</label>
                            <input class="small-input" type="number" name="price_per_night" min="1" step="0.01" value="1000" required>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 10px;">
                        <label>Description</label>
                        <textarea class="desc-input" name="description" rows="3" placeholder="Short description..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="margin-top: 10px;">Add Room</button>
                </form>
            </div>

            <div class="dashboard-section">
                <div class="section-header">
                    <h2>All Rooms</h2>
                    <p>Total: <?php echo count($rooms); ?> rooms</p>
                </div>

                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Room No</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Floor</th>
                                <th>Price</th>
                                <th>Description</th>
                                <th>Update</th>
                                <th>Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($rooms)): ?>
                                <tr><td colspan="9" class="text-center">No rooms found</td></tr>
                            <?php else: ?>
                                <?php foreach ($rooms as $r): ?>
                                    <tr>
                                        <td>#<?php echo $r['id']; ?></td>
                                        <td><strong><?php echo htmlspecialchars($r['room_number']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($r['room_type']); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo htmlspecialchars($r['status']); ?>">
                                                <?php echo ucfirst($r['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo intval($r['floor_number']); ?></td>
                                        <td>৳<?php echo number_format($r['price_per_night'], 2); ?></td>
                                        <td style="max-width:260px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="<?php echo htmlspecialchars($r['description'] ?? ''); ?>">
                                            <?php echo !empty($r['description']) ? htmlspecialchars($r['description']) : '—'; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-primary btn-small" onclick="openEditModal(
                                                <?php echo (int)$r['id']; ?>,
                                                '<?php echo htmlspecialchars($r['room_number'], ENT_QUOTES); ?>',
                                                '<?php echo htmlspecialchars($r['room_type'], ENT_QUOTES); ?>',
                                                '<?php echo htmlspecialchars($r['status'], ENT_QUOTES); ?>',
                                                <?php echo (int)$r['floor_number']; ?>,
                                                <?php echo (float)$r['price_per_night']; ?>,
                                                '<?php echo htmlspecialchars($r['description'] ?? '', ENT_QUOTES); ?>'
                                            )">Edit</button>
                                        </td>
                                        <td>
                                            <a class="btn btn-danger btn-small"
                                               href="?delete=<?php echo (int)$r['id']; ?>"
                                               onclick="return confirm('Delete this room?');">Delete</a>
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

    
    <div id="editModal" class="modal" style="display:none; position:fixed; inset:0; background: rgba(0,0,0,.5); z-index:1000;">
        <div class="modal-content" style="background:white; max-width: 650px; width: 92%; margin: 5% auto; border-radius: 8px; padding: 20px;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h2 style="margin:0;">Edit Room</h2>
                <button class="btn btn-outline btn-small" onclick="closeEditModal()">Close</button>
            </div>

            <form method="POST" style="margin-top: 15px;">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="room_id" id="edit_room_id">

                <div class="mini-form-grid">
                    <div class="form-group">
                        <label>Room Number *</label>
                        <input class="small-input" type="text" name="room_number" id="edit_room_number" required>
                    </div>
                    <div class="form-group">
                        <label>Room Type *</label>
                        <input class="small-input" type="text" name="room_type" id="edit_room_type" required>
                        <small style="color:#777;">You can type: Single / Double / Suite / Deluxe etc.</small>
                    </div>
                    <div class="form-group">
                        <label>Status *</label>
                        <select class="small-input" name="status" id="edit_status" required>
                            <option value="available">Available</option>
                            <option value="booked">Booked</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Floor *</label>
                        <input class="small-input" type="number" name="floor_number" id="edit_floor" min="1" required>
                    </div>
                    <div class="form-group">
                        <label>Price per Night *</label>
                        <input class="small-input" type="number" name="price_per_night" id="edit_price" min="1" step="0.01" required>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 10px;">
                    <label>Description</label>
                    <textarea class="desc-input" name="description" id="edit_description" rows="3"></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="margin-top: 10px;">Save Changes</button>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(id, roomNo, type, status, floor, price, desc) {
            document.getElementById('edit_room_id').value = id;
            document.getElementById('edit_room_number').value = roomNo;
            document.getElementById('edit_room_type').value = type;
            document.getElementById('edit_status').value = status;
            document.getElementById('edit_floor').value = floor;
            document.getElementById('edit_price').value = price;
            document.getElementById('edit_description').value = desc || '';
            document.getElementById('editModal').style.display = 'block';
        }
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        window.onclick = function(e) {
            const modal = document.getElementById('editModal');
            if (e.target === modal) closeEditModal();
        }
    </script>
</body>
</html>

