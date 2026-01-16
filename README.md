# Hotel Management System

A comprehensive web-based hotel management system built with PHP, MySQL, and modern web technologies. This system provides complete functionality for managing hotel operations including room bookings, customer management, staff administration, and service requests.

## 🚀 Features

### For Customers
- **User Registration & Authentication** - Secure signup and login with password hashing
- **Room Search & Booking** - Browse available rooms, filter by date, and make reservations
- **Booking Management** - View, modify, and cancel bookings
- **Service Requests** - Request room service, housekeeping, or maintenance
- **Reviews & Ratings** - Submit reviews and ratings for completed stays
- **Complaints & Feedback** - Submit complaints or feedback to hotel administration
- **Invoice Generation** - View and download booking invoices

### For Staff
- **Booking Management** - Process customer bookings, view booking details
- **Customer Management** - Search customers, view customer information
- **Service Request Handling** - View and manage service requests
- **Room Status Updates** - Update room availability and status

### For Administrators
- **Complete Dashboard** - Overview of bookings, revenue, and system statistics
- **Room Management** - Add, edit, delete, and manage room types and availability
- **Booking Management** - View all bookings, update status, modify bookings
- **Customer Management** - Full CRUD operations for customer accounts
- **Staff Management** - Manage staff accounts, roles, and performance
- **Complaint Management** - Handle customer complaints and feedback
- **Service Request Management** - Assign and track service requests
- **Reports & Analytics** - Generate reports on bookings, revenue, and occupancy

## 🛠️ Technologies

- **Backend**: PHP 7.4+
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript
- **Database Access**: PDO (PHP Data Objects)
- **Architecture**: MVC (Model-View-Controller) Pattern
- **Server**: Apache (XAMPP/WAMP/LAMP)

## 📋 Prerequisites

- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Apache Web Server
- XAMPP/WAMP/LAMP (recommended for local development)

## 🔧 Installation

### Step 1: Clone or Download the Project

```bash
git clone <repository-url>
cd Hotel-Management-System
```

Or download and extract the project to your web server directory:
- **XAMPP**: `C:\xampp\htdocs\Hotel-Management-System`
- **WAMP**: `C:\wamp64\www\Hotel-Management-System`
- **LAMP**: `/var/www/html/Hotel-Management-System`

### Step 2: Database Setup

1. Start your MySQL server (via XAMPP/WAMP/LAMP control panel)

2. Create the database and import tables:

   **Option A: Import Main Database Schema**
   ```sql
   -- Run this SQL file in phpMyAdmin or MySQL command line:
   db/database_setup.sql
   ```

   **Option B: Manual Setup**
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Create a new database named `hotel_management`
   - Import `db/database_setup.sql`
   - Import `db/new_features.sql` for additional features

3. Import additional SQL files as needed:
   - `admin/db/admin_table.sql` - Admin accounts
   - `staff/db/staff_table.sql` - Staff accounts
   - `rooms/db/rooms_table.sql` - Rooms table
   - `rooms/db/bookings_table.sql` - Bookings table

### Step 3: Configure Database Connection

Edit `config/database.php` and update the database credentials:

```php
private $host = "localhost";
private $db_name = "hotel_management";
private $username = "root";
private $password = "";  // Update with your MySQL password
```

### Step 4: Set Permissions

Ensure the following directories are writable:
- `logs/` - For email logs and other application logs

### Step 5: Access the Application

1. Start Apache and MySQL servers

2. Open your web browser and navigate to:
   - Landing Page: `http://localhost/Hotel-Management-System/landing/php/index.php`
   - Customer Login: `http://localhost/Hotel-Management-System/login_signup/php/login.php`
   - Admin Login: `http://localhost/Hotel-Management-System/admin/php/dashboard.php`
   - Staff Login: `http://localhost/Hotel-Management-System/staff/php/dashboard.php`

## 📁 Project Structure

```
Hotel-Management-System/
├── admin/                 # Admin panel module
│   ├── controllers/      # Admin controllers (MVC)
│   ├── models/           # Admin models
│   ├── php/              # Admin views/pages
│   ├── css/              # Admin styles
│   ├── js/               # Admin JavaScript
│   └── db/               # Admin SQL schemas
├── staff/                # Staff module
│   ├── controllers/      # Staff controllers
│   ├── models/           # Staff models
│   ├── php/              # Staff views/pages
│   ├── css/              # Staff styles
│   └── db/               # Staff SQL schemas
├── rooms/                # Room booking module
│   ├── controllers/      # Booking controllers
│   ├── models/           # Room and Booking models
│   ├── php/              # Booking views
│   ├── css/              # Booking styles
│   └── db/               # Room/Booking SQL schemas
├── login_signup/         # Authentication module
│   ├── controllers/      # Auth controller
│   ├── models/           # Customer model
│   ├── php/              # Login/Signup pages
│   └── css/              # Auth styles
├── landing/              # Landing page
│   ├── php/              # Landing page view
│   ├── css/              # Landing styles
│   └── js/               # Landing JavaScript
├── config/               # Configuration files
│   └── database.php      # Database connection config
├── utils/                # Utility functions
│   └── mailer.php        # Email utility
├── image/                # Images and assets
├── logs/                 # Application logs
├── db/                   # Database setup scripts
│   ├── database_setup.sql
│   └── new_features.sql
└── tools/                # Development tools
    └── strip_comments.php
```

## 🏗️ Architecture

The project follows the **MVC (Model-View-Controller)** architectural pattern:

- **Model**: Handles data logic and database operations (located in `*/models/`)
- **View**: Presentation layer - HTML/PHP templates (located in `*/php/`)
- **Controller**: Processes user input and coordinates between Model and View (located in `*/controllers/`)

## 🔐 Default Accounts

After setting up the database, you may need to create default accounts. Refer to the respective SQL files:
- Admin accounts: `admin/db/admin_table.sql`
- Staff accounts: `staff/db/staff_table.sql`

**Note**: Remember to change default passwords in production!

## 📧 Email Configuration

The system uses PHP's `mail()` function with a fallback to file logging. For local development:
- Emails are logged to `logs/emails.log` when `mail()` fails
- For production, configure SMTP in `utils/mailer.php`

## 🔒 Security Features

- ✅ Password hashing using `password_hash()` (bcrypt)
- ✅ SQL injection prevention with PDO prepared statements
- ✅ XSS prevention using `htmlspecialchars()`
- ✅ Input validation and sanitization
- ✅ Session-based authentication
- ✅ CSRF protection (where implemented)
- ✅ Role-based access control

## 📝 Database Schema

Key tables:
- `customers` - Customer accounts
- `rooms` - Room inventory
- `bookings` - Room reservations
- `staff` - Staff accounts
- `admin` - Administrator accounts
- `complaints` - Customer complaints/feedback
- `service_requests` - Service requests (room service, housekeeping, etc.)
- `reviews` - Customer reviews and ratings
- `booking_services` - Additional services (breakfast, parking, etc.)

## 🚦 Usage Guide

### For Customers

1. **Sign Up**: Create an account at the signup page
2. **Login**: Access your account
3. **Search Rooms**: Browse available rooms with date filters
4. **Make Booking**: Select room type, dates, and guests
5. **Manage Bookings**: View and manage your reservations
6. **Request Services**: Submit service requests during stay
7. **Submit Reviews**: Rate and review completed stays

### For Staff

1. **Login**: Access staff dashboard
2. **Process Bookings**: Create bookings for walk-in customers
3. **Search Customers**: Find customer information
4. **Handle Services**: View and manage service requests

### For Administrators

1. **Login**: Access admin dashboard
2. **Manage Rooms**: Add/edit room types and availability
3. **View Bookings**: Monitor all bookings and status
4. **Manage Customers**: View and edit customer accounts
5. **Manage Staff**: Create and manage staff accounts
6. **Handle Complaints**: Respond to customer complaints
7. **Generate Reports**: View analytics and reports

## 🐛 Troubleshooting

### Database Connection Error
- Verify MySQL server is running
- Check credentials in `config/database.php`
- Ensure database `hotel_management` exists

### Session Issues
- Ensure `session_start()` is called before output
- Check PHP session configuration
- Verify file permissions

### Email Not Sending
- Check `logs/emails.log` for email content
- Configure SMTP in production environment
- Verify PHP `mail()` configuration

## 📄 License

This project is for educational purposes. Please review and update license terms as needed.

## 👨‍💻 Development

### Code Standards
- Follow MVC architecture
- Use PDO for database operations
- Validate all user inputs
- Sanitize outputs to prevent XSS

### Adding New Features
1. Create model in appropriate `models/` directory
2. Create controller in `controllers/` directory
3. Create view in `php/` directory
4. Add necessary SQL migrations in `db/` directory
5. Update navigation and permissions as needed

## 📞 Support

For issues and questions:
- Check the module-specific README files (e.g., `login_signup/README.md`)
- Review SQL schema files in `db/` directory
- Examine code comments for implementation details

## 🔄 Version History

- **v1.0** - Initial release with core booking functionality
- Features: Room booking, customer management, admin panel, staff module, service requests, reviews

---

**Note**: This is a development project. Ensure proper security hardening before deploying to production.
