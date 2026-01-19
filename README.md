## Hotel Management System

A full-stack **PHP & MySQL Hotel Management System** for managing room reservations, customers, and admin operations.  
Designed to run on **XAMPP (Windows)**, this project includes:

- **Public landing page** for browsing rooms and hotel services
- **Customer authentication** (signup/login/logout)
- **Room browsing & booking** flow
- **Admin dashboard** to manage rooms, bookings, customers, and reports

---

## Tech Stack

- **Backend**: PHP (procedural + basic MVC-style controllers/models)
- **Frontend**: HTML5, CSS3, JavaScript
- **Database**: MySQL
- **Server environment**: XAMPP (Apache + MySQL) on Windows

---

## Project Structure

- **`landing/`**: Public-facing landing page
  - `landing/php/index.php` – main public entry point
  - `landing/css/styles.css` – landing page styling
  - `landing/js/index.js` – landing interactions

- **`login_signup/`**: Customer authentication
  - `login_signup/php/login.php` – login page & logic
  - `login_signup/php/signup.php` – signup/registration
  - `login_signup/php/signup_success.php` – signup success screen
  - `login_signup/php/logout.php` – logout flow
  - `login_signup/controllers/AuthController.php` – auth controller
  - `login_signup/models/Customer.php` – customer model

- **`admin/`**: Admin dashboard & management
  - `admin/php/dashboard.php` – admin home
  - `admin/php/bookings.php` – view/manage bookings
  - `admin/php/customers.php` – manage customers
  - `admin/php/manage-rooms.php` / `admin/php/rooms-crud.php` – room CRUD
  - `admin/php/reports.php` – reports & analytics
  - `admin/php/service-requests.php`, `admin/php/staff-performance.php` – additional admin features
  - `admin/controllers/*.php` – admin controllers (auth, bookings, etc.)
  - `admin/models/*.php` – admin models (Room, Booking, Customer, Admin)

- **`rooms/`**: Room listing and booking flow (customer side)
  - `rooms/php/` – room listing, details, filters, and booking actions
  - `rooms/controllers/` – room-related controllers
  - `rooms/models/` – room & booking-related models
  - `rooms/db/*.sql` – room-related seed data or schema

- **`config/`**
  - `config/database.php` – central database connection configuration

- **`db/`**
  - `db/database_setup.sql` – main database schema & seed data
  - `db/new_features.sql` – additional/updated tables/data

- **`image/`**
  - Hotel & room images used on landing/rooms/admin UI

- **`utils/`**
  - `utils/CookieManager.php` – helper for cookie handling
  - `utils/mailer.php` – email sending utility

- **`logs/`**
  - `logs/emails.log` – email-related logs

- **`tools/`**
  - `tools/strip_comments.php` – dev utility script
  - `tools/_comment_backups/` – internal backups (not needed in production)

---

## Prerequisites

- **XAMPP** installed on Windows (Apache + MySQL)
- **PHP** 7.4+ (bundled with recent XAMPP versions)
- **MySQL** 5.7+ / MariaDB (via XAMPP)
- A web browser (Chrome / Edge / Firefox)

---

## Setup Instructions (XAMPP on Windows)

1. **Clone or copy the project**
   - Place the folder in your XAMPP `htdocs` directory, e.g.  
     `C:\xampp\htdocs\Hotel-Management-System`

2. **Start Apache and MySQL**
   - Open the XAMPP Control Panel
   - Start **Apache** and **MySQL**

3. **Create the database**
   - Open `phpMyAdmin` (usually at `http://localhost/phpmyadmin`)
   - Create a new database (e.g. `hotel_management`)
   - Import the main schema:
     - Go to the **Import** tab
     - Select `db/database_setup.sql`
     - Run the import
   - (Optional) Import `db/new_features.sql` if you want extra tables/features

4. **Configure database connection**
   - Open `config/database.php`
   - Adjust credentials if needed:
     - host (usually `localhost`)
     - database name (e.g. `hotel_management`)
     - username (default XAMPP is usually `root`)
     - password (often empty string `""` on local XAMPP)

5. **Access the application**
   - **Public landing page**:  
     `http://localhost/Hotel-Management-System/landing/php/index.php`
   - **Customer login / signup**:  
     `http://localhost/Hotel-Management-System/login_signup/php/login.php`  
     `http://localhost/Hotel-Management-System/login_signup/php/signup.php`
   - **Admin dashboard** (URL may vary depending on routing):  
     `http://localhost/Hotel-Management-System/admin/php/dashboard.php`

---

## Core Features

- **Landing page**
  - Highlight hotel facilities, rooms, services, and hero images.

- **User authentication**
  - Customer **signup**, **login**, and **logout**
  - Session-based authentication (via controllers + models)

- **Room management (customer side)**
  - Browse rooms with images and details
  - Check availability and initiate bookings

- **Admin dashboard**
  - View and manage **bookings**
  - Manage **rooms** (add/edit/delete)
  - Manage **customers**
  - View **reports** (bookings, revenue, occupancy, etc.)
  - Manage **service requests** and **staff performance** (where implemented)

- **Utilities**
  - Email notifications (via `utils/mailer.php`, logging to `logs/emails.log`)
  - Cookie handling and small JS helpers

---

## Configuration & Environment

- **Database**
  - All connection details centralized in `config/database.php`
  - SQL schema and seed data are in the `db/` folder

- **Email**
  - `utils/mailer.php` may need SMTP credentials or mail configuration
  - Check this file to configure your own mail server (or disable emails in dev)
  - Email logs written to `logs/emails.log`

- **Sessions & cookies**
  - PHP sessions used for logged-in users
  - Some functionality relies on cookies (see `utils/CookieManager.php` and `admin/js/cookies.js`)

---

## Development Notes

- This project is structured in a **simple MVC-inspired pattern**:
  - **Controllers** handle request routing and high-level logic
  - **Models** represent database entities (Customer, Room, Booking, Admin, etc.)
  - **PHP views** mix HTML + PHP for output
- Frontend assets (`css`, `js`, `image`) are grouped within their respective modules (`landing`, `login_signup`, `admin`, `rooms`).

---

## How to Customize

- **Branding & UI**
  - Update styles in:
    - `landing/css/styles.css`
    - `login_signup/css/styles.css`
    - `admin/css/styles.css`
    - `rooms/css/styles.css`
  - Replace images in the `image/` directory with your own assets

- **Room types / amenities**
  - Update related database tables via `phpMyAdmin` or adjust the SQL in `db/database_setup.sql` and `rooms/db/*.sql`

- **Navigation & URLs**
  - If you rename folders or files, update links in the PHP views (especially in `landing/php/index.php`, `login_signup/php/*.php`, `admin/php/*.php`, and `rooms/php/*.php`)

---

## Security & Production Considerations

This project is primarily structured for **learning / local demo** purposes.  
Before deploying to production, review and improve:

- **Input validation & sanitization** (forms, GET/POST data)
- **Password hashing** and secure auth flows
- **CSRF protection** for forms
- **Error handling and logging**
- **Hiding debug info** and sensitive configuration

---

## Troubleshooting

- **Blank page / PHP errors**
  - Check Apache/PHP error log from XAMPP
  - Enable `display_errors` in `php.ini` for local debugging

- **Database connection errors**
  - Confirm `config/database.php` credentials match your local MySQL setup
  - Ensure database name matches what you created/imported in `phpMyAdmin`

- **Missing images / CSS / JS**
  - Confirm project folder name is exactly `Hotel-Management-System` under `htdocs`
  - Verify asset paths in the PHP files (relative URLs)


