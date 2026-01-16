<?php
session_start();
require_once __DIR__ . '/../controllers/AdminAuthController.php';
require_once __DIR__ . '/../../config/database.php';

AdminAuthController::requireLogin();

$db = new Database();
$conn = $db->getConnection();

$message = '';
$error = '';

 
$tableReady = false;
try {
    $chk = $conn->prepare("SHOW TABLES LIKE 'service_requests'");
    $chk->execute();
    $tableReady = $chk->rowCount() > 0;
} catch (Exception $e) {
    $tableReady = false;
}

 
$staffList = [];
try {
    $stmt = $conn->prepare("SELECT id, full_name, username FROM staff ORDER BY full_name");
    $stmt->execute();
    $staffList = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $staffList = [];
}

 
if ($tableReady && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    $rid = intval($_POST['request_id'] ?? 0);
    $status = $_POST['status'] ?? 'pending';
    $staffId = intval($_POST['assigned_staff_id'] ?? 0);
    $notes = trim($_POST['staff_notes'] ?? '');

    $validStatuses = ['pending', 'in-progress', 'completed'];
    if ($rid <= 0) {
        $error = "Invalid request id";
    } elseif (!in_array($status, $validStatuses)) {
        $error = "Invalid status";
    } else {
        try {
            $sid = ($staffId > 0) ? $staffId : null;
            $stmt = $conn->prepare("UPDATE service_requests
                                    SET assigned_staff_id = :sid,
                                        status = :st,
                                        staff_notes = :notes,
                                        updated_at = NOW()
                                    WHERE id = :id");
            if ($sid === null) {
                $stmt->bindValue(':sid', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':sid', $sid, PDO::PARAM_INT);
            }
            $stmt->bindValue(':st', $status);
            $stmt->bindValue(':notes', $notes);
            $stmt->bindValue(':id', $rid);
            $stmt->execute();
            $message = "Request updated";
        } catch (Exception $e) {
            $error = "Failed to update request";
        }
    }
}

 
if ($tableReady) {
    try {
        $conn->prepare("UPDATE service_requests SET assigned_staff_id = NULL WHERE assigned_staff_id = 0")->execute();
    } catch (Exception $e) {
         
    }
}

 
$requests = [];
if ($tableReady) {
    try {
        $sql = "SELECT sr.*, c.first_name, c.last_name, c.email, r.room_number,
                       s.full_name as staff_name
                FROM service_requests sr
                LEFT JOIN customers c ON sr.customer_id = c.id
                LEFT JOIN rooms r ON sr.room_id = r.id
                LEFT JOIN staff s ON sr.assigned_staff_id = s.id
                ORDER BY sr.created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $requests = [];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Requests - Admin</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .req { background: white; border: 1px solid #eee; border-radius: 10px; padding: 12px; }
        .inp { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; }
        .status { display:inline-block; padding: 4px 10px; border-radius: 999px; font-size: 12px; background:#f2f2f2; }
    </style>
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <div class="header-content">
                <h1>Service Requests (Admin)</h1>
                <div class="header-actions">
                    <span class="admin-name">Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                    <a href="dashboard.php" class="btn btn-outline">Dashboard</a>
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

            <?php if (!$tableReady): ?>
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2>Setup Needed</h2>
                        <p>Please run <code>db/new_features.sql</code> (service_requests table).</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2>All Requests</h2>
                        <p>Total: <?php echo count($requests); ?></p>
                    </div>

                    <?php if (empty($requests)): ?>
                        <div class="req">No requests found.</div>
                    <?php else: ?>
                        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 12px;">
                            <?php foreach ($requests as $r): ?>
                                <div class="req">
                                    <div style="display:flex; justify-content:space-between; gap:10px;">
                                        <strong>#<?php echo (int)$r['id']; ?></strong>
                                        <span class="status"><?php echo htmlspecialchars($r['status']); ?></span>
                                    </div>
                                    <div style="margin-top: 8px;">
                                        <div><strong>Type:</strong> <?php echo htmlspecialchars($r['request_type']); ?></div>
                                        <div><strong>Customer:</strong> <?php echo htmlspecialchars(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')); ?></div>
                                        <div><strong>Email:</strong> <?php echo htmlspecialchars($r['email'] ?? ''); ?></div>
                                        <div><strong>Room:</strong> <?php echo htmlspecialchars($r['room_number'] ?? 'N/A'); ?></div>
                                    </div>
                                    <div style="margin-top: 8px; color:#555;">
                                        <?php echo htmlspecialchars($r['description']); ?>
                                    </div>
                                    <div style="margin-top: 8px; font-size: 12px; color:#777;">
                                        Created: <?php echo date('M d, Y H:i', strtotime($r['created_at'])); ?>
                                    </div>

                                    <form method="POST" style="margin-top: 10px;">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="request_id" value="<?php echo (int)$r['id']; ?>">

                                        <div class="form-group">
                                            <label>Assign Staff</label>
                                            <select class="inp" name="assigned_staff_id">
                                                <option value="0">(Unassigned)</option>
                                                <?php foreach ($staffList as $s): ?>
                                                    <option value="<?php echo (int)$s['id']; ?>" <?php echo ((int)$r['assigned_staff_id'] === (int)$s['id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($s['full_name'] . ' (' . $s['username'] . ')'); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Status</label>
                                            <select class="inp" name="status" required>
                                                <option value="pending" <?php echo ($r['status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                                <option value="in-progress" <?php echo ($r['status'] === 'in-progress') ? 'selected' : ''; ?>>In-progress</option>
                                                <option value="completed" <?php echo ($r['status'] === 'completed') ? 'selected' : ''; ?>>Completed</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Staff Notes (message to customer)</label>
                                            <textarea class="inp" name="staff_notes" rows="3"><?php echo htmlspecialchars($r['staff_notes'] ?? ''); ?></textarea>
                                        </div>

                                        <button class="btn btn-primary btn-small" type="submit">Save</button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>

