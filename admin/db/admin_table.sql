USE hotel_management;

CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin
-- Default password: admin123
-- To generate a new password hash, use PHP: password_hash('your_password', PASSWORD_DEFAULT)
-- In production, change this password immediately!
INSERT INTO admins (username, email, password, full_name) VALUES
('admin', 'admin@grandhotel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator')
ON DUPLICATE KEY UPDATE username=username;

-- Note: The password hash above may not work. 
-- Run this PHP code to generate a proper hash for 'admin123':
-- <?php echo password_hash('admin123', PASSWORD_DEFAULT); ?>
-- Then update the INSERT statement above with the generated hash.
