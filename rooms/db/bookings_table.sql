USE hotel_management;

CREATE TABLE IF NOT EXISTS bookings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    room_id INT UNSIGNED NOT NULL,
    room_type VARCHAR(50) NOT NULL,
    check_in_date DATE NOT NULL,
    check_out_date DATE NOT NULL,
    number_of_guests INT NOT NULL DEFAULT 1,
    total_price DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') NOT NULL DEFAULT 'pending',
    special_requests TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_customer_id (customer_id),
    INDEX idx_room_id (room_id),
    INDEX idx_check_in_date (check_in_date),
    INDEX idx_check_out_date (check_out_date),
    INDEX idx_status (status),
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    CONSTRAINT chk_dates_valid CHECK (check_out_date > check_in_date),
    CONSTRAINT chk_guests_positive CHECK (number_of_guests > 0),
    CONSTRAINT chk_price_positive CHECK (total_price >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

