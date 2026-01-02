<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_name = $_SESSION['user_name'] ?? 'Guest';
$user_email = $_SESSION['user_email'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful - Grand Hotel</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="auth-container">
        <div class="success-card">
            <div class="success-icon">
                <svg width="80" height="80" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="10" stroke="#2e7d32" stroke-width="2" fill="#e8f5e9"/>
                    <path d="M8 12l2 2 4-4" stroke="#2e7d32" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>

            <div class="success-header">
                <h1>Welcome to Grand Hotel!</h1>
                <h2>Registration Successful</h2>
            </div>

            <div class="success-content">
                <p class="success-message">
                    Thank you, <strong><?php echo htmlspecialchars($user_name); ?></strong>! 
                    Your account has been successfully created.
                </p>

                <div class="success-benefits">
                    <h3>What's Next?</h3>
                    <ul class="benefits-list">
                        <li>
                            <span class="benefit-icon">✓</span>
                            <span>Explore our luxurious rooms and suites</span>
                        </li>
                        <li>
                            <span class="benefit-icon">✓</span>
                            <span>Book your stay with exclusive member rates</span>
                        </li>
                        <li>
                            <span class="benefit-icon">✓</span>
                            <span>Enjoy special discounts and promotions</span>
                        </li>
                        <li>
                            <span class="benefit-icon">✓</span>
                            <span>Access to members-only amenities</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="success-actions">
                <a href="../../Landing/php/index.php" class="btn btn-primary btn-full">
                    Continue to Homepage
                </a>
                <a href="../../Landing/php/index.php#rooms" class="btn btn-outline btn-full">
                    Browse Rooms
                </a>
            </div>

            <div class="success-footer">
                <p>Need help? <a href="../../Landing/php/index.php#contact" class="link">Contact Us</a></p>
            </div>
        </div>
    </div>

    <script src="../js/auth.js"></script>
</body>
</html>

