<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login_signup/php/login.php?redirect=profile');
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$db = new Database();
$conn = $db->getConnection();
$customerId = intval($_SESSION['user_id']);

$message = '';
$error = '';

$customer = null;
try {
    $stmt = $conn->prepare("SELECT id, first_name, last_name, email, phone, created_at FROM customers WHERE id = :id LIMIT 1");
    $stmt->bindValue(':id', $customerId);
    $stmt->execute();
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Failed to load profile";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    
    if (empty($first_name) || empty($last_name)) {
        $error = "First name and last name are required";
    } else {
        try {
            $stmt = $conn->prepare("UPDATE customers SET first_name = :first_name, last_name = :last_name, phone = :phone WHERE id = :id");
            $stmt->bindValue(':first_name', htmlspecialchars($first_name));
            $stmt->bindValue(':last_name', htmlspecialchars($last_name));
            $stmt->bindValue(':phone', htmlspecialchars($phone));
            $stmt->bindValue(':id', $customerId);
            
            if ($stmt->execute()) {
                if (!empty($current_password) && !empty($new_password)) {
                    $stmt = $conn->prepare("SELECT password FROM customers WHERE id = :id LIMIT 1");
                    $stmt->bindValue(':id', $customerId);
                    $stmt->execute();
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($user && password_verify($current_password, $user['password'])) {
                        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                        $stmt = $conn->prepare("UPDATE customers SET password = :password WHERE id = :id");
                        $stmt->bindValue(':password', $hashed);
                        $stmt->bindValue(':id', $customerId);
                        $stmt->execute();
                        $message = "Profile and password updated successfully";
                    } else {
                        $error = "Current password is incorrect";
                    }
                } else {
                    $message = "Profile updated successfully";
                }
                
                $stmt = $conn->prepare("SELECT id, first_name, last_name, email, phone, created_at FROM customers WHERE id = :id LIMIT 1");
                $stmt->bindValue(':id', $customerId);
                $stmt->execute();
                $customer = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $error = "Failed to update profile";
            }
        } catch (Exception $e) {
            $error = "Failed to update profile: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="rooms-container">
        <header class="rooms-header">
            <h1>Grand Hotel</h1>
            <h2>My Profile</h2>
            <div class="header-actions">
                <a href="../../landing/php/index.php" class="btn-back">← Back to Home</a>
                <a href="rooms.php" class="btn-back" style="margin-left: 10px;">Rooms</a>
                <a href="my-bookings.php" class="btn-back" style="margin-left: 10px;">My Bookings</a>
            </div>
        </header>

        <main style="max-width: 800px; margin: 30px auto; padding: 20px;">
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

            <?php if ($customer): ?>
                <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <h2 style="margin-bottom: 20px; color: #8B0000;">Personal Information</h2>
                    
                    <form method="POST">
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: bold;">First Name *</label>
                            <input type="text" name="first_name" value="<?php echo htmlspecialchars($customer['first_name']); ?>" required 
                                   style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Last Name *</label>
                            <input type="text" name="last_name" value="<?php echo htmlspecialchars($customer['last_name']); ?>" required 
                                   style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Email</label>
                            <input type="email" value="<?php echo htmlspecialchars($customer['email']); ?>" disabled 
                                   style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; background: #f5f5f5;">
                            <small style="color: #666;">Email cannot be changed</small>
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Phone</label>
                            <input type="tel" name="phone" value="<?php echo htmlspecialchars($customer['phone'] ?? ''); ?>" 
                                   style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                        </div>

                        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
                            <h3 style="margin-bottom: 15px;">Change Password (Optional)</h3>
                            <div style="margin-bottom: 15px;">
                                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Current Password</label>
                                <input type="password" name="current_password" 
                                       style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                            </div>
                            <div style="margin-bottom: 15px;">
                                <label style="display: block; margin-bottom: 5px; font-weight: bold;">New Password</label>
                                <input type="password" name="new_password" 
                                       style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                            </div>
                            <small style="color: #666;">Leave blank to keep current password</small>
                        </div>

                        <div style="margin-top: 30px;">
                            <button type="submit" name="update_profile" 
                                    style="background: #8B0000; color: white; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
                                Update Profile
                            </button>
                        </div>
                    </form>

                    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 14px;">
                        <p><strong>Account Created:</strong> <?php echo date('M d, Y', strtotime($customer['created_at'])); ?></p>
                    </div>
                </div>
            <?php else: ?>
                <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px;">
                    Failed to load profile information.
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
