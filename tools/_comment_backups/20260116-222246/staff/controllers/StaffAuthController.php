<?php

require_once __DIR__ . '/../models/Staff.php';

class StaffAuthController {
    private $staff;

    public function __construct() {
        $this->staff = new Staff();
    }

    


    public function loginByEmail($email, $password) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->staff->email = $email;
        $this->staff->password = $password;

        if (empty($this->staff->email)) {
            return false;
        }

        if (empty($this->staff->password)) {
            return false;
        }

        if ($this->staff->loginByEmail()) {
            $_SESSION['staff_id'] = $this->staff->id;
            $_SESSION['staff_username'] = $this->staff->username;
            $_SESSION['staff_name'] = $this->staff->full_name;
            $_SESSION['staff_email'] = $this->staff->email;
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
            $this->staff->username = $_POST['username'] ?? '';
            $this->staff->password = $_POST['password'] ?? '';

            if (empty($this->staff->username)) {
                $errors[] = "Username is required.";
            }

            if (empty($this->staff->password)) {
                $errors[] = "Password is required.";
            }

            if (empty($errors)) {
                if ($this->staff->login()) {
                    $_SESSION['staff_id'] = $this->staff->id;
                    $_SESSION['staff_username'] = $this->staff->username;
                    $_SESSION['staff_name'] = $this->staff->full_name;
                    $_SESSION['staff_email'] = $this->staff->email;
                    header("Location: dashboard.php");
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
        return isset($_SESSION['staff_id']);
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
        
        // Clear remember me cookie if exists
        require_once __DIR__ . '/../../utils/CookieManager.php';
        CookieManager::clearRememberMe();
        
        session_unset();
        session_destroy();
        header("Location: ../../login_signup/php/login.php");
        exit();
    }
}
?>

