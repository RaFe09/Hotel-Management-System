<?php
session_start();
require_once __DIR__ . '/../controllers/AdminAuthController.php';
require_once __DIR__ . '/../controllers/AdminBookingController.php';

AdminAuthController::requireLogin();

$controller = new AdminBookingController();
$message = '';
$error = '';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $result = $controller->deleteCustomer($_GET['delete']);
    if ($result['success']) {
        $message = $result['message'];
    } else {
        $error = implode(', ', $result['errors']);
    }
}

$customers = $controller->getAllCustomers();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Customers</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <div class="header-content">
                <h1>Manage Customers</h1>
                <div class="header-actions">
                    <a href="dashboard.php" class="btn btn-outline">Dashboard</a>
                    <a href="logout.php" class="btn btn-outline">Logout</a>
                </div>
            </div>
        </header>

        <main class="admin-main">
            <?php if ($message): ?>
                <div style="background: #d4edda; color: #155724; padding: 15px; margin: 20px 0; border-radius: 5px;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div style="background: #f8d7da; color: #721c24; padding: 15px; margin: 20px 0; border-radius: 5px;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="dashboard-section">
                <div class="section-header">
                    <h2>All Customers (<?php echo count($customers); ?>)</h2>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($customers)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No customers found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($customers as $customer): ?>
                                    <tr>
                                        <td>#<?php echo $customer['id']; ?></td>
                                        <td><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                        <td><?php echo htmlspecialchars($customer['phone'] ?? 'N/A'); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></td>
                                        <td>
                                            <a href="?delete=<?php echo $customer['id']; ?>" 
                                               onclick="return confirm('Delete this customer?');"
                                               style="color:#dc3545; text-decoration:none; padding:5px 10px; border:1px solid #dc3545; border-radius:3px; font-size:12px;">
                                                Delete
                                            </a>
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
</body>
</html>
