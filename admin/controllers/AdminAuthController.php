<?php

require_once __DIR__ . '/../models/Admin.php';

class AdminAuthController {
    private $admin;

    public function __construct() {
        $this->admin = new Admin();
    }

    


    public function loginByEmail($email, $password) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->admin->email = $email;
        $this->admin->password = $password;

        if (empty($this->admin->email)) {
            return false;
        }

        if (empty($this->admin->password)) {
            return false;
        }

        if ($this->admin->loginByEmail()) {
            $_SESSION['admin_id'] = $this->admin->id;
            $_SESSION['admin_username'] = $this->admin->username;
            $_SESSION['admin_name'] = $this->admin->full_name;
            $_SESSION['admin_email'] = $this->admin->email;
            return true;
        }
        
        return false;
    }

    


    public function login() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $errors = [];

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $this->admin->username = $_POST['username'] ?? '';
            $this->admin->password = $_POST['password'] ?? '';

            if (empty($this->admin->username)) {
                $errors[] = "Username is required.";
            }

            if (empty($this->admin->password)) {
                $errors[] = "Password is required.";
            }

            if (empty($errors)) {
                if ($this->admin->login()) {
                    $_SESSION['admin_id'] = $this->admin->id;
                    $_SESSION['admin_username'] = $this->admin->username;
                    $_SESSION['admin_name'] = $this->admin->full_name;
                    $_SESSION['admin_email'] = $this->admin->email;
                    header("Location: ../../admin/php/dashboard.php");
                    exit();
                } else {
                    $errors[] = "Invalid username or password.";
                }
            }
        }

        return $errors;
    }

    


    public static function isLoggedIn() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['admin_id']);
    }

    


    public static function requireLogin() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!self::isLoggedIn()) {
            header("Location: ../../login_signup/php/login.php");
            exit();
        }
    }

    


    public function logout() {
        session_start();
        session_unset();
        session_destroy();
        header("Location: ../../login_signup/php/login.php");
        exit();
    }
}
?>
