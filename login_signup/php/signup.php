<?php

session_start();
require_once __DIR__ . '/../controllers/AuthController.php';

$authController = new AuthController();
$errors = $authController->signup();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Grand Hotel</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Grand Hotel</h1>
                <h2>Create Account</h2>
                <p>Join us for exclusive benefits</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="error-messages">
                    <?php foreach ($errors as $error): ?>
                        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="auth-form" id="signupForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input 
                            type="text" 
                            id="first_name" 
                            name="first_name" 
                            placeholder="Enter your first name"
                            value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input 
                            type="text" 
                            id="last_name" 
                            name="last_name" 
                            placeholder="Enter your last name"
                            value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>"
                            required
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="Enter your email"
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input 
                        type="tel" 
                        id="phone" 
                        name="phone" 
                        placeholder="Enter your phone number"
                        value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Create a password (min. 6 characters)"
                        required
                    >
                    <div class="password-toggle" id="passwordToggle">
                        <span class="toggle-icon">👁️</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        placeholder="Confirm your password"
                        required
                    >
                    <div class="password-toggle" id="confirmPasswordToggle">
                        <span class="toggle-icon">👁️</span>
                    </div>
                </div>

                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="terms" required>
                        <span>I agree to the <a href="#" class="link">Terms & Conditions</a></span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-full">Create Account</button>
            </form>

            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Sign In</a></p>
                <p><a href="../Landing/php/index.php" class="back-link">← Back to Home</a></p>
            </div>
        </div>
    </div>

    <script src="../js/auth.js"></script>
</body>
</html>

