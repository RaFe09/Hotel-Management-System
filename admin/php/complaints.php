<?php
session_start();
require_once __DIR__ . '/../controllers/AdminAuthController.php';
require_once __DIR__ . '/../models/Complaint.php';

AdminAuthController::requireLogin();

$complaintModel = new Complaint();
$message = '';
$error = '';

 
$tableReady = $complaintModel->tableExists();

 
if ($tableReady && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'reply') {
    $id = intval($_POST['complaint_id'] ?? 0);
    $status = $_POST['status'] ?? 'pending';
    $reply = trim($_POST['admin_reply'] ?? '');

    $validStatuses = ['pending', 'in-progress', 'resolved'];
    if ($id <= 0) {
        $error = "Invalid complaint id";
    } elseif (!in_array($status, $validStatuses)) {
        $error = "Invalid status";
    } else {
        if ($complaintModel->updateReplyAndStatus($id, $status, $reply)) {
            $message = "Reply saved";
        } else {
            $error = "Failed to save reply";
        }
    }
}

$complaints = $tableReady ? $complaintModel->getAll() : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complaints & Feedback - Admin</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <div class="header-content">
                <h1>Complaints & Feedback</h1>
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
                        <p>
                            The <code>complaints</code> table is not in your database yet.
                            Please import the new SQL file we will add in <code>db/new_features.sql</code>.
                        </p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="dashboard-section">
                <div class="section-header">
                    <h2>All Complaints / Feedback</h2>
                    <p>Total: <?php echo count($complaints); ?></p>
                </div>

                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Type</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($complaints)): ?>
                                <tr><td colspan="7" class="text-center">No complaints/feedback found</td></tr>
                            <?php else: ?>
                                <?php foreach ($complaints as $c): ?>
                                    <tr>
                                        <td>#<?php echo (int)$c['id']; ?></td>
                                        <td>
                                            <?php echo htmlspecialchars(($c['first_name'] ?? '') . ' ' . ($c['last_name'] ?? '')); ?><br>
                                            <small><?php echo htmlspecialchars($c['email'] ?? ''); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($c['type'] ?? 'complaint'); ?></td>
                                        <td title="<?php echo htmlspecialchars($c['message'] ?? ''); ?>">
                                            <?php echo htmlspecialchars($c['subject'] ?? ''); ?>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo htmlspecialchars(strtolower(str_replace('-', '', $c['status'] ?? 'pending'))); ?>">
                                                <?php echo htmlspecialchars($c['status'] ?? 'pending'); ?>
                                            </span>
                                        </td>
                                        <td><?php echo !empty($c['created_at']) ? date('M d, Y', strtotime($c['created_at'])) : '—'; ?></td>
                                        <td>
                                            <button class="btn btn-primary btn-small" onclick="openReplyModal(<?php echo htmlspecialchars(json_encode($c)); ?>)">Reply / Update</button>
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

    
    <div id="replyModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeReplyModal()">&times;</span>
            <h2>Reply / Update Status</h2>
            <div style="margin-bottom: 10px; color:#555;">
                <div><strong id="m_customer"></strong></div>
                <div id="m_subject"></div>
                <div style="margin-top:6px;"><small id="m_message"></small></div>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="reply">
                <input type="hidden" name="complaint_id" id="m_id">

                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="m_status" required>
                        <option value="pending">Pending</option>
                        <option value="in-progress">In-progress</option>
                        <option value="resolved">Resolved</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Admin Reply</label>
                    <textarea name="admin_reply" id="m_reply" rows="4" placeholder="Write a short reply..."></textarea>
                </div>

                <div style="display:flex; gap:10px; margin-top: 15px;">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <button type="button" class="btn btn-outline" onclick="closeReplyModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openReplyModal(c) {
            document.getElementById('m_id').value = c.id;
            document.getElementById('m_status').value = c.status || 'pending';
            document.getElementById('m_reply').value = c.admin_reply || '';

            const name = (c.first_name || '') + ' ' + (c.last_name || '');
            document.getElementById('m_customer').textContent = name.trim() || 'Customer';
            document.getElementById('m_subject').textContent = 'Subject: ' + (c.subject || '');
            document.getElementById('m_message').textContent = 'Message: ' + (c.message || '');

            document.getElementById('replyModal').style.display = 'block';
        }
        function closeReplyModal() {
            document.getElementById('replyModal').style.display = 'none';
        }
        window.onclick = function(event) {
            const modal = document.getElementById('replyModal');
            if (event.target == modal) closeReplyModal();
        }
    </script>
</body>
</html>

