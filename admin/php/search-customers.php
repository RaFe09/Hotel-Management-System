<?php
session_start();

header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../controllers/AdminAuthController.php';
    require_once __DIR__ . '/../controllers/AdminBookingController.php';
    AdminAuthController::requireLogin();
    $controller = new AdminBookingController();

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
