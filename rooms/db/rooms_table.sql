USE hotel_management;

DROP TABLE IF EXISTS rooms;

CREATE TABLE rooms (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    room_number VARCHAR(10) NOT NULL UNIQUE,
    room_type VARCHAR(50) NOT NULL,
    status ENUM('available', 'booked', 'maintenance') NOT NULL DEFAULT 'available',
    floor_number TINYINT UNSIGNED NOT NULL,
    price_per_night DECIMAL(10, 2) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_room_number (room_number),
    INDEX idx_status (status),
    INDEX idx_room_type (room_type),
    INDEX idx_floor_number (floor_number),
    INDEX idx_status_floor (status, floor_number),
    CONSTRAINT chk_price_positive CHECK (price_per_night > 0),
    CONSTRAINT chk_floor_positive CHECK (floor_number > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO rooms (room_number, room_type, status, floor_number, price_per_night) VALUES
    ('201', 'Deluxe Room', 'available', 2, 3500.00),
    ('202', 'Deluxe Room', 'available', 2, 3500.00),
    ('203', 'Deluxe Room', 'available', 2, 3500.00),
    ('204', 'Deluxe Room', 'available', 2, 3500.00),
    ('205', 'Deluxe Room', 'booked', 2, 3500.00),
    ('206', 'Deluxe Room', 'available', 2, 3500.00),
    ('207', 'Deluxe Room', 'available', 2, 3500.00),
    ('208', 'Deluxe Room', 'maintenance', 2, 3500.00),
    ('209', 'Deluxe Room', 'available', 2, 3500.00),
    ('210', 'Deluxe Room', 'available', 2, 3500.00);

INSERT INTO rooms (room_number, room_type, status, floor_number, price_per_night) VALUES
    ('301', 'Deluxe Room', 'available', 3, 3500.00),
    ('302', 'Deluxe Room', 'available', 3, 3500.00),
    ('303', 'Deluxe Room', 'booked', 3, 3500.00),
    ('304', 'Deluxe Room', 'available', 3, 3500.00),
    ('305', 'Deluxe Room', 'available', 3, 3500.00),
    ('306', 'Deluxe Room', 'available', 3, 3500.00),
    ('307', 'Deluxe Room', 'available', 3, 3500.00);

INSERT INTO rooms (room_number, room_type, status, floor_number, price_per_night) VALUES
    ('308', 'Executive Suite', 'available', 3, 7500.00),
    ('309', 'Executive Suite', 'booked', 3, 7500.00),
    ('310', 'Executive Suite', 'available', 3, 7500.00);

INSERT INTO rooms (room_number, room_type, status, floor_number, price_per_night) VALUES
    ('401', 'Executive Suite', 'available', 4, 7500.00),
    ('402', 'Executive Suite', 'available', 4, 7500.00),
    ('403', 'Executive Suite', 'booked', 4, 7500.00),
    ('404', 'Executive Suite', 'available', 4, 7500.00),
    ('405', 'Executive Suite', 'available', 4, 7500.00),
    ('406', 'Executive Suite', 'maintenance', 4, 7500.00),
    ('407', 'Executive Suite', 'available', 4, 7500.00);

INSERT INTO rooms (room_number, room_type, status, floor_number, price_per_night) VALUES
    ('501', 'Executive Suite', 'available', 5, 7500.00),
    ('502', 'Executive Suite', 'available', 5, 7500.00),
    ('503', 'Executive Suite', 'available', 5, 7500.00),
    ('504', 'Executive Suite', 'booked', 5, 7500.00),
    ('505', 'Executive Suite', 'available', 5, 7500.00),
    ('506', 'Executive Suite', 'available', 5, 7500.00),
    ('507', 'Executive Suite', 'available', 5, 7500.00);

INSERT INTO rooms (room_number, room_type, status, floor_number, price_per_night) VALUES
    ('601', 'Presidential Suite', 'available', 6, 12000.00),
    ('602', 'Presidential Suite', 'available', 6, 12000.00),
    ('603', 'Presidential Suite', 'booked', 6, 12000.00),
    ('604', 'Presidential Suite', 'available', 6, 12000.00),
    ('605', 'Presidential Suite', 'available', 6, 12000.00),
    ('606', 'Presidential Suite', 'available', 6, 12000.00),
    ('607', 'Presidential Suite', 'available', 6, 12000.00),
    ('608', 'Presidential Suite', 'booked', 6, 12000.00),
    ('609', 'Presidential Suite', 'available', 6, 12000.00),
    ('610', 'Presidential Suite', 'available', 6, 12000.00),
    ('611', 'Presidential Suite', 'available', 6, 12000.00),
    ('612', 'Presidential Suite', 'maintenance', 6, 12000.00),
    ('613', 'Presidential Suite', 'available', 6, 12000.00),
    ('614', 'Presidential Suite', 'available', 6, 12000.00),
    ('615', 'Presidential Suite', 'available', 6, 12000.00),
    ('616', 'Presidential Suite', 'available', 6, 12000.00);

INSERT INTO rooms (room_number, room_type, status, floor_number, price_per_night) VALUES
    ('701', 'Romantic Suite', 'available', 7, 15000.00),
    ('702', 'Romantic Suite', 'available', 7, 15000.00),
    ('703', 'Romantic Suite', 'available', 7, 15000.00),
    ('704', 'Romantic Suite', 'available', 7, 15000.00),
    ('705', 'Romantic Suite', 'available', 7, 15000.00);

INSERT INTO rooms (room_number, room_type, status, floor_number, price_per_night) VALUES
    ('801', 'Romantic Suite', 'available', 8, 15000.00),
    ('802', 'Romantic Suite', 'available', 8, 15000.00),
    ('803', 'Romantic Suite', 'available', 8, 15000.00),
    ('804', 'Romantic Suite', 'available', 8, 15000.00),
    ('805', 'Romantic Suite', 'available', 8, 15000.00);