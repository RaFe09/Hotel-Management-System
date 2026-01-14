# Admin Panel - Hotel Management System

Admin panel following MVC architecture for managing hotel bookings and customers.

## Features

- ✅ Admin authentication system
- ✅ Admin dashboard with statistics
- ✅ Book rooms for customers (including new customers)
- ✅ View recent bookings
- ✅ Automatic customer creation for new bookings

## Setup

### 1. Database Setup

Run the admin table SQL file to create the admin table:

```sql
-- Run this file in your MySQL database
admin/db/admin_table.sql
```

Or import it via command line:
```bash
mysql -u root -p hotel_management < admin/db/admin_table.sql
```

### 2. Default Admin Credentials

- **Email:** `admin@grandhotel.com`
- **Password:** `admin123`

**⚠️ IMPORTANT:** Change the default password immediately after first login in production!

To generate a new admin password hash, you can use:
```php
<?php
echo password_hash('your_new_password', PASSWORD_DEFAULT);
?>
```

Then update the `admins` table with the new hash.

### 3. Directory Structure

```
admin/
├── config/
│   └── database.php          # Database configuration
├── controllers/
│   ├── AdminAuthController.php    # Admin authentication
│   └── AdminBookingController.php # Booking management
├── models/
│   ├── Admin.php             # Admin model
│   ├── Booking.php           # Booking model
│   ├── Customer.php          # Customer model (extended for admin)
│   └── Room.php              # Room model
├── php/
│   ├── logout.php            # Logout handler
│   ├── dashboard.php         # Admin dashboard
│   └── book-room.php         # Book room for customer
├── css/
│   └── styles.css            # Admin panel styles
├── js/
│   └── admin.js              # Admin JavaScript
└── db/
    └── admin_table.sql       # Database setup

```

## Usage

### Accessing Admin Panel

1. Navigate to: `login_signup/php/login.php`
2. Login with admin email and password (Email: `admin@grandhotel.com`, Password: `admin123`)
3. You'll be redirected to the dashboard

**Note:** The system uses a unified login page located at `login_signup/php/login.php`. The system automatically detects if the email belongs to an admin or a regular user and redirects accordingly. Admins are redirected to the admin dashboard, while regular users are redirected to the landing page.

### Booking Rooms for Customers

1. Click "Book Room" button from the dashboard
2. Fill in customer information:
   - If customer email exists, the booking will be linked to existing customer
   - If customer email is new, a new customer account will be created automatically
3. Select room type, dates, number of guests
4. Submit the form
5. Booking will be confirmed immediately

### Features

- **Automatic Customer Creation:** When booking for a new customer, the system automatically creates a customer account with a random password
- **Customer Lookup:** System checks if customer exists by email before creating
- **Room Availability:** System automatically finds available rooms for the selected dates
- **Booking Confirmation:** Bookings are immediately confirmed (status: 'confirmed')

## Security

- Passwords are hashed using PHP's `password_hash()` function
- SQL injection prevention using PDO prepared statements
- XSS prevention using `htmlspecialchars()`
- Session-based authentication
- Admin-only access protection

## MVC Architecture

This admin panel follows the MVC (Model-View-Controller) pattern:

- **Models:** Data access layer (`models/`)
- **Views:** Presentation layer (`php/`)
- **Controllers:** Business logic layer (`controllers/`)

## Database Tables Used

- `admins` - Admin users
- `customers` - Customer information
- `rooms` - Room information
- `bookings` - Booking records

## Notes

- Admin-created customers get a random password generated automatically
- The system does not store or display the generated password for security reasons
- Customers created by admin can reset their password through the customer portal (if implemented)
- All bookings are immediately confirmed (status: 'confirmed')
