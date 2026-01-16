<?php
session_start();

 
$isAdmin = isset($_SESSION['admin_id']);
$isStaff = isset($_SESSION['staff_id']);

header('Content-Type: application/json');

try {
    if ($isAdmin) {
        require_once __DIR__ . '/../controllers/AdminAuthController.php';
        require_once __DIR__ . '/../controllers/AdminBookingController.php';
        AdminAuthController::requireLogin();
        $controller = new AdminBookingController();
    } elseif ($isStaff) {
        require_once __DIR__ . '/../../staff/controllers/StaffAuthController.php';
        require_once __DIR__ . '/../../staff/controllers/StaffBookingController.php';
        StaffAuthController::requireLogin();
        $controller = new StaffBookingController();
    } else {
         
        echo json_encode([]);
        exit();
    }

    $searchTerm = trim($_GET['q'] ?? '');

    if (strlen($searchTerm) >= 2) {
        $results = $controller->searchCustomers($searchTerm);
        echo json_encode($results ? $results : []);
    } else {
        echo json_encode([]);
    }
} catch (Exception $e) {
     
    error_log("Search customers error: " . $e->getMessage());
    echo json_encode([]);
}
?>
