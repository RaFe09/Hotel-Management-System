<?php
session_start();
require_once __DIR__ . '/../controllers/AdminAuthController.php';
require_once __DIR__ . '/../models/Staff.php';

AdminAuthController::requireLogin();

$staffModel = new Staff();

$message = '';
$error = '';

function validateStaff($data, $isCreate = true) {
    $errors = [];

    if (empty(trim($data['username'] ?? ''))) $errors[] = "Username is required";
    if (empty(trim($data['email'] ?? ''))) $errors[] = "Email is required";
    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email";
    if (empty(trim($data['full_name'] ?? ''))) $errors[] = "Full name is required";

    if ($isCreate) {
        if (empty($data['password'] ?? '')) $errors[] = "Password is required";
        if (!empty($data['password']) && strlen($data['password']) < 4) $errors[] = "Password too short";
    } else {
         
        if (!empty($data['password']) && strlen($data['password']) < 4) $errors[] = "Password too short";
    }

    return $errors;
}

 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $errors = validateStaff($_POST, true);
    if (!empty($errors)) {
        $error = implode(', ', $errors);
    } else {
        if ($staffModel->create($_POST)) {
            $message = "Staff account created";
        } else {
            $error = "Failed to create staff (maybe duplicate username/email?)";
        }
    }
}

 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    $id = intval($_POST['staff_id'] ?? 0);
    if ($id <= 0) {
        $error = "Invalid staff id";
    } else {
        $errors = validateStaff($_POST, false);
        if (!empty($errors)) {
            $error = implode(', ', $errors);
        } else {
            if ($staffModel->update($id, $_POST)) {
                $message = "Staff updated";
            } else {
                $error = "Failed to update staff";
            }
        }
    }
}

 
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($id > 0) {
        if ($staffModel->delete($id)) {
            $message = "Staff deleted";
        } else {
            $error = "Failed to delete staff";
        }
    }
}

$staffList = $staffModel->getAll();
$roles = ['receptionist', 'housekeeping', 'maintenance'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Management - Admin</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .mini-form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; }
        .small-input { padding: 10px; border: 1px solid #ddd; border-radius: 6px; width: 100%; }
    </style>
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <div class="header-content">
                <h1>Staff Management</h1>
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

            <div class="dashboard-section">
                <div class="section-header">
                    <h2>Add Staff</h2>
                    <p>Roles: receptionist / housekeeping / maintenance</p>
                </div>

                <form method="POST" style="padding: 15px; background: white; border-radius: 8px; border: 1px solid #eee;">
                    <input type="hidden" name="action" value="create">
                    <div class="mini-form-grid">
                        <div class="form-group">
                            <label>Username *</label>
                            <input class="small-input" type="text" name="username" required>
                        </div>
                        <div class="form-group">
                            <label>Email *</label>
                            <input class="small-input" type="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label>Full Name *</label>
                            <input class="small-input" type="text" name="full_name" required>
                        </div>
                        <div class="form-group">
                            <label>Password *</label>
                            <input class="small-input" type="text" name="password" placeholder="e.g. staff123" required>
                        </div>
                        <div class="form-group">
                            <label>Role (optional)</label>
                            <select class="small-input" name="role">
                                <option value="">(no role)</option>
                                <?php foreach ($roles as $r): ?>
                                    <option value="<?php echo $r; ?>"><?php echo ucfirst($r); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small style="color:#777;">If your DB has no <code>role</code> column yet, it will be ignored.</small>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="margin-top: 10px;">Create Staff</button>
                </form>
            </div>

            <div class="dashboard-section">
                <div class="section-header">
                    <h2>All Staff</h2>
                    <p>Total: <?php echo count($staffList); ?> staff</p>
                </div>

                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($staffList)): ?>
                                <tr><td colspan="7" class="text-center">No staff found</td></tr>
                            <?php else: ?>
                                <?php foreach ($staffList as $s): ?>
                                    <tr>
                                        <td>#<?php echo (int)$s['id']; ?></td>
                                        <td><?php echo htmlspecialchars($s['username']); ?></td>
                                        <td><?php echo htmlspecialchars($s['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($s['email']); ?></td>
                                        <td><?php echo htmlspecialchars($s['role'] ?? 'N/A'); ?></td>
                                        <td><?php echo !empty($s['created_at']) ? date('M d, Y', strtotime($s['created_at'])) : '—'; ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($s)); ?>)" class="btn btn-primary btn-small">Edit</button>
                                                <a href="?delete=<?php echo (int)$s['id']; ?>"
                                                   class="btn btn-danger btn-small"
                                                   onclick="return confirm('Delete this staff account?');">Delete</a>
                                            </div>
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

    
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>Edit Staff</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="staff_id" id="edit_staff_id">

                <div class="form-group">
                    <label>Username *</label>
                    <input type="text" class="small-input" name="username" id="edit_username" required>
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" class="small-input" name="email" id="edit_email" required>
                </div>
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" class="small-input" name="full_name" id="edit_full_name" required>
                </div>
                <div class="form-group">
                    <label>Role (optional)</label>
                    <select class="small-input" name="role" id="edit_role">
                        <option value="">(no role)</option>
                        <?php foreach ($roles as $r): ?>
                            <option value="<?php echo $r; ?>"><?php echo ucfirst($r); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>New Password (optional)</label>
                    <input type="text" class="small-input" name="password" id="edit_password" placeholder="Leave blank to keep same">
                </div>

                <div style="display:flex; gap:10px; margin-top: 15px;">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <button type="button" class="btn btn-outline" onclick="closeEditModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(staff) {
            document.getElementById('edit_staff_id').value = staff.id;
            document.getElementById('edit_username').value = staff.username;
            document.getElementById('edit_email').value = staff.email;
            document.getElementById('edit_full_name').value = staff.full_name;
            document.getElementById('edit_role').value = staff.role || '';
            document.getElementById('edit_password').value = '';
            document.getElementById('editModal').style.display = 'block';
        }
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target == modal) closeEditModal();
        }
    </script>
</body>
</html>

