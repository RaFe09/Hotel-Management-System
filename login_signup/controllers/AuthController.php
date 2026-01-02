<?php

require_once __DIR__ . '/../models/Customer.php';

class AuthController {
    private $customer;

    public function __construct() {
        $this->customer = new Customer();
    }

    public function signup() {
        $errors = [];

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $this->customer->first_name = $_POST['first_name'] ?? '';
            $this->customer->last_name = $_POST['last_name'] ?? '';
            $this->customer->email = $_POST['email'] ?? '';
            $this->customer->phone = $_POST['phone'] ?? '';
            $this->customer->password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            if (empty($this->customer->first_name)) {
                $errors[] = "First name is required.";
            }

            if (empty($this->customer->last_name)) {
                $errors[] = "Last name is required.";
            }

            if (empty($this->customer->email)) {
                $errors[] = "Email is required.";
            } elseif (!filter_var($this->customer->email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Invalid email format.";
            } elseif ($this->customer->emailExists()) {
                $errors[] = "Email already exists.";
            }

            if (empty($this->customer->phone)) {
                $errors[] = "Phone number is required.";
            }

            if (empty($this->customer->password)) {
                $errors[] = "Password is required.";
            } elseif (strlen($this->customer->password) < 6) {
                $errors[] = "Password must be at least 6 characters.";
            }

            if ($this->customer->password !== $confirm_password) {
                $errors[] = "Passwords do not match.";
            }

            if (empty($errors)) {
                if ($this->customer->create()) {
                    $_SESSION['user_id'] = $this->customer->id;
                    $_SESSION['user_name'] = $this->customer->first_name . ' ' . $this->customer->last_name;
                    $_SESSION['user_email'] = $this->customer->email;
                    header("Location: signup_success.php");
                    exit();
                } else {
                    $errors[] = "Registration failed. Please try again.";
                }
            }
        }

        return $errors;
    }

    public function login() {
        $errors = [];

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $this->customer->email = $_POST['email'] ?? '';
            $this->customer->password = $_POST['password'] ?? '';

            if (empty($this->customer->email)) {
                $errors[] = "Email is required.";
            }

            if (empty($this->customer->password)) {
                $errors[] = "Password is required.";
            }

            if (empty($errors)) {
                if ($this->customer->login()) {
                    $_SESSION['user_id'] = $this->customer->id;
                    $_SESSION['user_name'] = $this->customer->first_name . ' ' . $this->customer->last_name;
                    $_SESSION['user_email'] = $this->customer->email;
                    header("Location: ../Landing/php/index.php");
                    exit();
                } else {
                    $errors[] = "Invalid email or password.";
                }
            }
        }

        return $errors;
    }

    public function logout() {
        session_start();
        session_unset();
        session_destroy();
        header("Location: login.php");
        exit();
    }
}
?>

