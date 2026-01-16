<?php
session_start();
require_once __DIR__ . '/../controllers/AdminAuthController.php';
require_once __DIR__ . '/../../config/database.php';

AdminAuthController::requireLogin();

$db = new Database();
$conn = $db->getConnection();

 
$tableReady = false;
try {
    $chk = $conn->prepare("SHOW TABLES LIKE 'service_requests'");
    $chk->execute();
    $tableReady = $chk->rowCount() > 0;
} catch (Exception $e) {
    $tableReady = false;
}

 
$rows = [];
if ($tableReady) {
    try {
        $sql = "SELECT s.id, s.full_name, s.username,
                       COUNT(sr.id) as total_assigned,
                       SUM(CASE WHEN sr.status = 'completed' THEN 1 ELSE 0 END) as completed_count
                FROM staff s
                LEFT JOIN service_requests sr ON sr.assigned_staff_id = s.id
                GROUP BY s.id, s.full_name, s.username
                ORDER BY completed_count DESC, total_assigned DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $rows = [];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Performance - Admin</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <div class="header-content">
                <h1>Staff Performance</h1>
                <div class="header-actions">
                    <span class="admin-name">Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                    <a href="dashboard.php" class="btn btn-outline">Dashboard</a>
                    <a href="logout.php" class="btn btn-outline">Logout</a>
                </div>
            </div>
        </header>

        <main class="admin-main">
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
                        <h2>Requests Completed (Simple)</h2>
                        <p>This is a basic performance view based on service requests.</p>
                    </div>

                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Staff</th>
                                    <th>Username</th>
                                    <th>Total Assigned</th>
                                    <th>Completed</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($rows)): ?>
                                    <tr><td colspan="4" class="text-center">No data</td></tr>
                                <?php else: ?>
                                    <?php foreach ($rows as $r): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($r['full_name']); ?></td>
                                            <td><?php echo htmlspecialchars($r['username']); ?></td>
                                            <td><?php echo (int)$r['total_assigned']; ?></td>
                                            <td><?php echo (int)$r['completed_count']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>

