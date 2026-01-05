<?php

session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: ../Landing/php/index.php");
    exit();
}
if (isset($_SESSION['admin_id'])) {
    header("Location: ../../admin/php/dashboard.php");
    exit();
}

require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../../admin/controllers/AdminAuthController.php';
require_once __DIR__ . '/../models/Customer.php';

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email)) {
        $errors[] = "Email is required.";
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    if (empty($errors)) {
        // Try admin login first
        $adminAuthController = new AdminAuthController();
        if ($adminAuthController->loginByEmail($email, $password)) {
            // Admin login successful - redirect handled in loginByEmail
            header("Location: ../../admin/php/dashboard.php");
            exit();
        }
        
        // If not admin, try user login
        $authController = new AuthController();
        $customer = new Customer();
        $customer->email = $email;
        $customer->password = $password;
        
        if ($customer->login()) {
            $_SESSION['user_id'] = $customer->id;
            $_SESSION['user_name'] = $customer->first_name . ' ' . $customer->last_name;
            $_SESSION['user_email'] = $customer->email;
            header("Location: ../Landing/php/index.php");
            exit();
        } else {
            $errors[] = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Grand Hotel</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Grand Hotel</h1>
                <h2>Welcome Back</h2>
                <p>Sign in to your account</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="error-messages">
                    <?php foreach ($errors as $error): ?>
                        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="auth-form" id="loginForm">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="Enter your email"
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                        required
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Enter your password"
                        required
                    >
                    <div class="password-toggle" id="passwordToggle">
                        <span class="toggle-icon">👁️</span>
                    </div>
                </div>

                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember_me">
                        <span>Remember me</span>
                    </label>
                    <a href="#" class="forgot-password">Forgot Password?</a>
                </div>

                <button type="submit" class="btn btn-primary btn-full">Sign In</button>
            </form>

            <div class="auth-footer">
                <p>Don't have an account? <a href="signup.php">Sign Up</a></p>
                <p><a href="../../Landing/php/index.php" class="back-link">← Back to Home</a></p>
            </div>
        </div>
    </div>

    <script src="../js/auth.js"></script>
</body>
</html>

