<?php

session_start();
require_once __DIR__ . '/../controllers/AdminAuthController.php';

$authController = new AdminAuthController();
$authController->logout();
?>
