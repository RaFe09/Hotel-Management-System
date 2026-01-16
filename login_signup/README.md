# Login & Signup Module

This module follows the MVC (Model-View-Controller) architecture pattern, similar to the Landing page structure.

## Folder Structure

```
login_signup/
├── models/
│   └── Customer.php          # Customer model (database operations)
├── controllers/
│   └── AuthController.php   # Authentication controller (business logic)
├── php/
│   ├── login.php             # Login view
│   ├── signup.php            # Signup view
│   └── logout.php            # Logout handler
├── css/
│   └── styles.css            # Styling for auth pages
├── js/
│   └── auth.js               # JavaScript for form validation
└── README.md                 # This file
```

## MVC Architecture

### Model (models/Customer.php)
- Handles database operations
- Contains business logic for customer data
- Methods: `emailExists()`, `create()`, `login()`

### View (php/login.php, php/signup.php)
- Contains HTML/PHP presentation layer
- Displays forms and error messages
- Handles user interface

### Controller (controllers/AuthController.php)
- Processes form submissions
- Validates input data
- Coordinates between Model and View
- Handles authentication flow

## Database Setup

Before using this module, create the following database table:

```sql
CREATE DATABASE IF NOT EXISTS hotel_management;

USE hotel_management;

CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## Configuration

Update database credentials in `../../config/database.php` (shared configuration):
- `$host`: Database host (default: localhost)
- `$db_name`: Database name (default: hotel_management)
- `$username`: Database username (default: root)
- `$password`: Database password (default: empty)

## Usage

### Login
- URL: `login_signup/php/login.php`
- Requires: Email and Password

### Sign Up
- URL: `login_signup/php/signup.php`
- Requires: First Name, Last Name, Email, Phone, Password, Confirm Password

### Logout
- URL: `login_signup/php/logout.php`
- Clears session and redirects to login

## Features

- ✅ MVC architecture
- ✅ Form validation (client-side and server-side)
- ✅ Password hashing (bcrypt)
- ✅ Email uniqueness check
- ✅ ✅ Session management
- ✅ Responsive design
- ✅ Password visibility toggle
- ✅ Error handling and display

## Security Features

- Password hashing using PHP `password_hash()`
- Password verification using PHP `password_verify()`
- SQL injection prevention using PDO prepared statements
- XSS prevention using `htmlspecialchars()`
- Input sanitization
- Session-based authentication

