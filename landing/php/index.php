<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grand Hotel - Luxury Hotel in Dhaka</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-wrapper">
                <div class="logo">
                    <a href="#home">Grand Hotel</a>
                </div>
                <ul class="nav-menu" id="navMenu">
                    <li><a href="#overview">Overview</a></li>
                    <li><a href="#rooms">Rooms</a></li>
                    <li><a href="#dining">Dining</a></li>
                    <li><a href="#amenities">Amenities</a></li>
                    <li><a href="#location">Location</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="user-menu">
                            <span class="user-name">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                            <a href="../../login_signup/php/logout.php" class="btn-signin">Logout</a>
                        </li>
                    <?php else: ?>
                        <li><a href="../../login_signup/php/login.php" class="btn-signin">Sign In or Join</a></li>
                    <?php endif; ?>
                </ul>
                <div class="hamburger" id="hamburger">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>
    </nav>

    <section id="home" class="hero-section">
        <div class="hero-image" style="background-image: url('../../image/Hotel Exterior.jpg');">
            <div class="hero-overlay"></div>
            <div class="hero-content">
                <h1>Grand Hotel</h1>
                <p class="hotel-location">Dhaka, Bangladesh</p>
                <div class="hero-rating">
                    <span class="stars">★★★★★</span>
                    <span class="rating-text">4.4 Guest Rating</span>
                </div>
                <div class="hero-button">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="../../rooms/php/rooms.php" class="btn btn-primary">Book Now</a>
                        <a href="../../rooms/php/my-bookings.php" class="btn btn-primary" style="margin-left: 10px;">My Bookings</a>
                    <?php else: ?>
                        <a href="../../rooms/php/rooms.php" class="btn btn-primary">Book Now</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <section id="overview" class="overview-section">
        <div class="container">
            <div class="overview-content">
                <div class="overview-text">
                    <h2>Welcome to Grand Hotel</h2>
                    <p class="overview-description">
                        Experience unparalleled luxury in the heart of the city. Grand Hotel combines timeless elegance with modern sophistication, offering an exceptional stay for business and leisure travelers alike. Our award-winning property features world-class amenities, exquisite dining, and personalized service that exceeds expectations.
                    </p>
                    <p class="overview-description">
                        Located in the vibrant Gulshan area of Dhaka, Grand Hotel provides easy access to shopping, entertainment, and cultural attractions. Whether you're here for a romantic getaway, business meeting, or family vacation, we ensure every moment of your stay is memorable.
                    </p>
                    <div class="hotel-highlights">
                        <div class="highlight-item">
                            <span class="highlight-icon">🏆</span>
                            <div>
                                <strong>Award-Winning</strong>
                                <p>Luxury Hotel of the Year</p>
                            </div>
                        </div>
                        <div class="highlight-item">
                            <span class="highlight-icon">📍</span>
                            <div>
                                <strong>Prime Location</strong>
                                <p>Gulshan, Dhaka</p>
                            </div>
                        </div>
                        <div class="highlight-item">
                            <span class="highlight-icon">⭐</span>
                            <div>
                                <strong>5-Star Service</strong>
                                <p>Excellence Guaranteed</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="overview-image">
                    <img src="../../image/Hotel Exterior.jpg" alt="Grand Hotel Exterior" class="overview-img">
                </div>
            </div>
        </div>
    </section>

    <section id="honeymoon" class="honeymoon-package">
        <div class="container">
            <div class="package-header">
                <span class="package-badge">Special Package</span>
                <h2>Romantic Honeymoon & Couples Getaway</h2>
                <p>Create unforgettable memories with our exclusive couples package in the Romantic Suite</p>
            </div>
            <div class="package-content">
                <div class="package-image">
                    <img src="../../image/Romantic Suite.jpg" alt="Romantic Suite" class="package-img">
                </div>
                <div class="package-details">
                    <h3>Package Includes</h3>
                    <ul class="package-features">
                        <li>Romantic Suite with romantic room setup and rose petals</li>
                        <li>Champagne and chocolate-covered strawberries on arrival</li>
                        <li>Couples spa treatment session (60 minutes)</li>
                        <li>Romantic candlelit dinner for two at our signature restaurant</li>
                        <li>Late checkout until 2 PM and breakfast in bed</li>
                        <li>Complimentary room upgrade (subject to availability)</li>
                        <li>Welcome gift basket with local delicacies</li>
                    </ul>
                    <div class="package-price">
                        <span class="price-label">Starting from</span>
                        <div class="price-main">
                            <span class="price-amount">৳15,000</span>
                            <span class="price-period">per night</span>
                        </div>
                    </div>
                    <div class="package-buttons">
                        <a href="#contact" class="btn btn-primary">Book This Package</a>
                        <a href="#contact" class="btn btn-outline">View Details</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="rooms" class="rooms-section">
        <div class="container">
            <div class="section-header">
                <h2>Accommodations</h2>
                <p>Luxurious rooms and suites designed for your comfort</p>
            </div>
            <div class="rooms-grid">
                <div class="room-card">
                    <div class="room-image">
                        <img src="../../image/Deluxe Room.png" alt="Deluxe Room" class="room-img">
                    </div>
                    <div class="room-info">
                        <h3>Deluxe Room</h3>
                        <p class="room-size">350 sq ft</p>
                        <p class="room-description">Spacious room with city views, plush bedding, and modern amenities.</p>
                        <div class="room-features">
                            <span>King Bed</span>
                            <span>City View</span>
                            <span>Wi-Fi</span>
                        </div>
                        <div class="room-price">From ৳3,500/night</div>
                    </div>
                </div>
                <div class="room-card">
                    <div class="room-image">
                        <img src="../../image/Executive Suite.jpg" alt="Executive Suite" class="room-img">
                    </div>
                    <div class="room-info">
                        <h3>Executive Suite</h3>
                        <p class="room-size">550 sq ft</p>
                        <p class="room-description">Elegant suite with separate living area and premium amenities.</p>
                        <div class="room-features">
                            <span>King Bed</span>
                            <span>Living Room</span>
                            <span>Premium</span>
                        </div>
                        <div class="room-price">From ৳7,500/night</div>
                    </div>
                </div>
                <div class="room-card">
                    <div class="room-image">
                        <img src="../../image/Presidential Suite.jpg" alt="Presidential Suite" class="room-img">
                    </div>
                    <div class="room-info">
                        <h3>Presidential Suite</h3>
                        <p class="room-size">1,200 sq ft</p>
                        <p class="room-description">Ultimate luxury with panoramic views and butler service.</p>
                        <div class="room-features">
                            <span>King Bed</span>
                            <span>Panoramic View</span>
                            <span>Butler</span>
                        </div>
                        <div class="room-price">From ৳12,000/night</div>
                    </div>
                </div>
                <div class="room-card">
                    <div class="room-image">
                        <img src="../../image/Romantic Suite.jpg" alt="Romantic Suite" class="room-img">
                    </div>
                    <div class="room-info">
                        <h3>Romantic Suite</h3>
                        <p class="room-size">650 sq ft</p>
                        <p class="room-description">Intimate and luxurious escape designed for couples with elegant décor.</p>
                        <div class="room-features">
                            <span>King Bed</span>
                            <span>Jacuzzi</span>
                            <span>Romantic</span>
                        </div>
                        <div class="room-price">From ৳15,000/night</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="dining" class="dining-section">
        <div class="container">
            <div class="section-header">
                <h2>Dining & Bars</h2>
                <p>Exceptional culinary experiences await</p>
            </div>
            <div class="dining-grid">
                <div class="dining-card">
                    <div class="dining-image">
                        <img src="../../image/The Grand Restaurant.jpg" alt="The Grand Restaurant" class="dining-img">
                    </div>
                    <div class="dining-info">
                        <h3>The Grand Restaurant</h3>
                        <p class="dining-type">Fine Dining</p>
                        <p>International cuisine with locally sourced ingredients. Open for breakfast, lunch, and dinner.</p>
                        <div class="dining-hours">Daily: 6:30 AM - 11:00 PM</div>
                    </div>
                </div>
                <div class="dining-card">
                    <div class="dining-image">
                        <img src="../../image/Sky Lounge.jpeg" alt="Sky Lounge" class="dining-img">
                    </div>
                    <div class="dining-info">
                        <h3>Sky Lounge</h3>
                        <p class="dining-type">Rooftop Bar</p>
                        <p>Signature cocktails and light bites with stunning city views. Perfect for evening relaxation.</p>
                        <div class="dining-hours">Daily: 5:00 PM - 1:00 AM</div>
                    </div>
                </div>
                <div class="dining-card">
                    <div class="dining-image">
                        <img src="../../image/Café Lobby.jpg" alt="Café Lobby" class="dining-img">
                    </div>
                    <div class="dining-info">
                        <h3>Café Lobby</h3>
                        <p class="dining-type">Casual Dining</p>
                        <p>Fresh pastries, premium coffee, and light meals in a relaxed atmosphere.</p>
                        <div class="dining-hours">Daily: 7:00 AM - 9:00 PM</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="amenities" class="amenities-section">
        <div class="container">
            <div class="section-header">
                <h2>Hotel Amenities</h2>
                <p>Everything you need for a perfect stay</p>
            </div>
            <div class="amenities-grid">
                <div class="amenity-item">
                    <div class="amenity-image">
                        <img src="../../image/Swimming Pool.jpg" alt="Swimming Pool" class="amenity-img">
                    </div>
                    <h4>Swimming Pool</h4>
                    <p>Outdoor pool with poolside service</p>
                </div>
                <div class="amenity-item">
                    <div class="amenity-image">
                        <img src="../../image/Spa & Wellness.jpg" alt="Spa & Wellness" class="amenity-img">
                    </div>
                    <h4>Spa & Wellness</h4>
                    <p>Full-service spa and fitness center</p>
                </div>
                <div class="amenity-item">
                    <div class="amenity-image">
                        <img src="../../image/Business Center.jpg" alt="Business Center" class="amenity-img">
                    </div>
                    <h4>Business Center</h4>
                    <p>Meeting rooms and business facilities</p>
                </div>
                <div class="amenity-item">
                    <div class="amenity-image">
                        <img src="../../image/Valet Parking.jpg" alt="Valet Parking" class="amenity-img">
                    </div>
                    <h4>Valet Parking</h4>
                    <p>Complimentary valet parking service</p>
                </div>
                <div class="amenity-item">
                    <div class="amenity-image">
                        <img src="../../image/Free Wi-Fi.jpg" alt="Free Wi-Fi" class="amenity-img">
                    </div>
                    <h4>Free Wi-Fi</h4>
                    <p>High-speed internet throughout</p>
                </div>
                <div class="amenity-item">
                    <div class="amenity-image">
                        <img src="../../image/Room Service.jpg" alt="Room Service" class="amenity-img">
                    </div>
                    <h4>Room Service</h4>
                    <p>24-hour in-room dining available</p>
                </div>
                <div class="amenity-item">
                    <div class="amenity-image">
                        <img src="../../image/Fitness Center.jpeg" alt="Fitness Center" class="amenity-img">
                    </div>
                    <h4>Fitness Center</h4>
                    <p>State-of-the-art equipment</p>
                </div>
                <div class="amenity-item">
                    <div class="amenity-image">
                        <img src="../../image/Concierge.jpg" alt="Concierge" class="amenity-img">
                    </div>
                    <h4>Concierge</h4>
                    <p>24-hour concierge service</p>
                </div>
            </div>
        </div>
    </section>

    <section id="location" class="location-section">
        <div class="container">
            <div class="location-content">
                <div class="location-info">
                    <h2>Location</h2>
                    <p class="location-address">
                        <strong>Grand Hotel</strong><br>
                        Road 45, Gulshan-2<br>
                        Dhaka 1212, Bangladesh
                    </p>
                    <div class="location-details">
                        <div class="location-item">
                            <strong>Distance from Airport:</strong> 12 km (30 minutes)
                        </div>
                        <div class="location-item">
                            <strong>Distance from City Center:</strong> 8 km (20 minutes)
                        </div>
                        <div class="location-item">
                            <strong>Nearby Attractions:</strong> Gulshan Lake Park, Shopping Malls, Restaurants
                        </div>
                    </div>
                    <div class="location-buttons">
                        <a href="#contact" class="btn btn-primary">Get Directions</a>
                        <a href="#contact" class="btn btn-outline">Contact Hotel</a>
                    </div>
                </div>
                <div class="location-map">
                    <img src="../../image/hotelview.jpg" alt="Hotel View and Location" class="location-img">
                </div>
            </div>
        </div>
    </section>

    <section id="contact" class="contact">
        <div class="container">
            <div class="section-header">
                <h2>Contact Us</h2>
                <p>We're here to help make your stay perfect</p>
            </div>
            <div class="contact-content">
                <div class="contact-info">
                    <div class="info-item">
                        <h4>Phone</h4>
                        <p>+880 1712-345678</p>
                    </div>
                    <div class="info-item">
                        <h4>Email</h4>
                        <p>reservations@grandhotel.com</p>
                    </div>
                    <div class="info-item">
                        <h4>Address</h4>
                        <p>Road 45, Gulshan-2<br>Dhaka 1212, Bangladesh</p>
                    </div>
                </div>
                <form class="contact-form" id="contactForm">
                    <div class="form-group">
                        <input type="text" name="name" placeholder="Your Name" required>
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" placeholder="Your Email" required>
                    </div>
                    <div class="form-group">
                        <input type="tel" name="phone" placeholder="Your Phone">
                    </div>
                    <div class="form-group">
                        <textarea name="message" rows="5" placeholder="Your Message" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Message</button>
                </form>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Grand Hotel</h3>
                    <p>Your luxury destination for unforgettable experiences in the heart of Dhaka.</p>
                </div>
                <div class="footer-section">
                    <h4>Hotel</h4>
                    <ul>
                        <li><a href="#overview">Overview</a></li>
                        <li><a href="#rooms">Rooms & Suites</a></li>
                        <li><a href="#dining">Dining</a></li>
                        <li><a href="#amenities">Amenities</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Services</h4>
                    <ul>
                        <li><a href="#honeymoon">Special Packages</a></li>
                        <li><a href="#contact">Reservations</a></li>
                        <li><a href="#contact">Meetings & Events</a></li>
                        <li><a href="#location">Location</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact</h4>
                    <ul>
                        <li>Phone: +880 1712-345678</li>
                        <li>Email: reservations@grandhotel.com</li>
                        <li>Road 45, Gulshan-2</li>
                        <li>Dhaka 1212, Bangladesh</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 Grand Hotel. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="../js/index.js"></script>
</body>
</html>
