<?php
$page_title = 'Home';
require_once 'includes/db.php';
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARMAS — Assistance and Repatriation Management and Action System</title>
    <meta name="description" content="ARMAS - Protecting Every Filipino, Every Mile Away">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@400;500;600&family=JetBrains+Mono&display=swap" rel="stylesheet">

    <!-- ARMAS CSS -->
    <link rel="stylesheet" href="/armas/assets/css/style.css">
    <link rel="stylesheet" href="/armas/assets/css/responsive.css">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/armas/assets/img/armas.jpg">

    <style>
        /* Landing Page Specific Styles */
        .landing-page {
            min-height: 100vh;
        }

        /* Navbar */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: var(--primary);
            z-index: 1000;
            box-shadow: var(--shadow-md);
        }

        .navbar-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 16px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .navbar-logo {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
        }

        .navbar-brand-text .logo-text {
            color: var(--white);
            font-size: 1.5rem;
        }

        .navbar-brand-text .brand-subtitle {
            display: block;
            font-size: 0.65rem;
            color: var(--secondary);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .hamburger {
            display: none;
            flex-direction: column;
            gap: 5px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px;
        }

        .hamburger span {
            display: block;
            width: 25px;
            height: 3px;
            background: var(--white);
            border-radius: 2px;
            transition: var(--transition);
        }

        .nav-menu {
            display: flex;
            align-items: center;
            gap: 24px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .nav-menu a {
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
            text-decoration: none;
            transition: var(--transition);
        }

        .nav-menu a:not(.btn):hover {
            color: var(--secondary);
        }

        .nav-menu .btn-outline-gold:hover {
            background: var(--secondary);
            color: var(--primary);
        }

        .nav-menu .btn-gold:hover {
            background: transparent;
            color: var(--secondary);
            border: 2px solid var(--secondary);
            text-decoration: none;
        }

        .nav-menu .btn {
            padding: 10px 20px;
        }

        /* Hero Section */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            position: relative;
            overflow: hidden;
            padding: 120px 24px 80px;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -30%;
            width: 800px;
            height: 800px;
            background: radial-gradient(circle, rgba(200, 169, 81, 0.15) 0%, transparent 70%);
            border-radius: 50%;
            animation: pulse 8s ease-in-out infinite;
        }

        .hero::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -20%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(232, 244, 253, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            animation: pulse 10s ease-in-out infinite reverse;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 0.5;
            }

            50% {
                transform: scale(1.1);
                opacity: 0.8;
            }
        }

        .hero-content {
            max-width: 800px;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            font-weight: 700;
            color: var(--white);
            margin-bottom: 24px;
            line-height: 1.2;
        }

        .hero p {
            font-size: 1.25rem;
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: 40px;
            line-height: 1.7;
        }

        .hero-buttons {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .hero .btn-gold {
            background: var(--secondary);
            color: var(--primary-dark);
            border: none;
        }

        .hero .btn-gold:hover {
            background: var(--secondary-light);
        }

        .hero .btn-outline-white {
            background: transparent;
            color: var(--white);
            border: 2px solid var(--white);
        }

        .hero .btn-outline-white:hover {
            background: var(--white);
            color: var(--primary);
        }

        /* Sections */
        .section {
            padding: 100px 24px;
        }

        .section-dark {
            background: var(--primary);
            color: var(--white);
        }

        .section-dark h2 {
            color: var(--white);
        }

        .section-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-header h2 {
            font-size: 2.5rem;
            margin-bottom: 16px;
        }

        .section-header p {
            font-size: 1.125rem;
            color: var(--mid);
            max-width: 600px;
            margin: 0 auto;
        }

        .section-dark .section-header p {
            color: rgba(255, 255, 255, 0.8);
        }

        /* About Section */
        .about-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }

        .about-image {
            position: relative;
        }

        .about-image img {
            width: 100%;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
        }

        .about-image::before {
            content: '';
            position: absolute;
            top: -20px;
            left: -20px;
            width: 100%;
            height: 100%;
            border: 3px solid var(--secondary);
            border-radius: var(--radius-lg);
            z-index: -1;
        }

        .about-content h3 {
            font-size: 1.75rem;
            margin-bottom: 20px;
        }

        .about-content p {
            color: var(--mid);
            margin-bottom: 16px;
            line-height: 1.7;
        }

        .about-features {
            list-style: none;
            margin-top: 24px;
        }

        .about-features li {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        .about-features li::before {
            content: '✓';
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            background: var(--success);
            color: var(--white);
            border-radius: 50%;
            font-size: 0.75rem;
            font-weight: bold;
        }

        /* Services Section */
        .services-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
        }

        .service-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 32px;
            text-align: center;
            box-shadow: var(--shadow-md);
            transition: var(--transition);
            border: 2px solid transparent;
        }

        .service-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
            border-color: var(--secondary);
        }

        .service-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: var(--accent);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
        }

        .service-card h3 {
            font-size: 1.25rem;
            margin-bottom: 12px;
        }

        .service-card p {
            color: var(--mid);
            font-size: 0.95rem;
            line-height: 1.6;
        }

        /* Contact Section */
        .contact-section {
            background: var(--light);
        }

        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
        }

        .contact-info h3 {
            font-size: 1.75rem;
            margin-bottom: 20px;
        }

        .contact-info p {
            color: var(--mid);
            margin-bottom: 24px;
        }

        .contact-details {
            list-style: none;
        }

        .contact-details li {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 16px;
            font-size: 1rem;
        }

        .contact-details .icon {
            width: 48px;
            height: 48px;
            background: var(--accent);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .contact-form {
            background: var(--white);
            padding: 32px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
        }

        .contact-form h3 {
            margin-bottom: 24px;
        }

        /* Terms Section */
        .terms-section {
            background: var(--light);
        }

        .terms-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .accordion {
            background: var(--white);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-md);
        }

        /* Footer */
        .footer {
            background: var(--primary-dark);
            color: var(--white);
            padding: 60px 24px 24px;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 40px;
        }

        .footer-brand {
            display: flex;
            align-items: flex-start;
            gap: 16px;
        }

        .footer-logo {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            object-fit: cover;
        }

        .footer-brand .logo-text {
            color: var(--white);
            font-size: 1.75rem;
            display: block;
            margin-bottom: 8px;
        }

        .footer-tagline {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.95rem;
        }

        .footer-links h4 {
            color: var(--secondary);
            font-size: 1rem;
            margin-bottom: 16px;
            font-family: 'DM Sans', sans-serif;
            font-weight: 600;
        }

        .footer-links ul {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 10px;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: var(--transition);
        }

        .footer-links a:hover {
            color: var(--secondary);
        }

        .footer-contact h4 {
            color: var(--secondary);
            font-size: 1rem;
            margin-bottom: 16px;
            font-family: 'DM Sans', sans-serif;
            font-weight: 600;
        }

        .footer-contact p {
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 8px;
        }

        .footer-bottom {
            max-width: 1200px;
            margin: 40px auto 0;
            padding-top: 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.875rem;
        }

        /* Mobile Styles */
        @media (max-width: 1024px) {
            .services-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .about-grid,
            .contact-grid {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .footer-container {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 768px) {
            .hamburger {
                display: flex;
            }

            .nav-menu {
                position: fixed;
                top: 80px;
                left: 0;
                right: 0;
                background: var(--primary);
                flex-direction: column;
                padding: 20px;
                gap: 0;
                transform: translateY(-100%);
                opacity: 0;
                visibility: hidden;
                transition: var(--transition);
            }

            .nav-menu.open {
                transform: translateY(0);
                opacity: 1;
                visibility: visible;
            }

            .nav-menu li {
                width: 100%;
            }

            .nav-menu a {
                display: block;
                padding: 12px 16px;
                border-radius: var(--radius-md);
            }

            .nav-menu a:hover {
                background: rgba(255, 255, 255, 0.1);
            }

            .hero h1 {
                font-size: 2.5rem;
            }

            .hero p {
                font-size: 1rem;
            }

            .hero-buttons {
                flex-direction: column;
            }

            .hero-buttons .btn {
                width: 100%;
            }

            .section {
                padding: 60px 20px;
            }

            .services-grid {
                grid-template-columns: 1fr;
            }

            .footer-container {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .footer-brand {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>

<body class="landing-page">

    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="/armas/pages/landing.php" class="navbar-brand">
                <img src="/armas/assets/img/armas.jpg" alt="ARMAS Shield" class="navbar-logo">
                <div class="navbar-brand-text">
                    <span class="logo-text">ARMAS</span>
                </div>
            </a>

            <button class="hamburger" id="hamburger" onclick="toggleMobileMenu()">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <ul class="nav-menu" id="nav-menu">
                <li><a href="#home">Home</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#services">Services</a></li>
                <li><a href="#contact">Contact</a></li>
                <li><a href="#terms">Terms</a></li>
                <li><a href="/armas/pages/login.php" class="btn btn-outline-gold">Login</a></li>
                <li><a href="/armas/pages/register.php" class="btn btn-gold">Register as OFW</a></li>
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-content">
            <h1>Protecting Every Filipino, Every Mile Away</h1>
            <p>ARMAS provides comprehensive assistance and repatriation services for Overseas Filipino Workers. Our mission is to ensure the safety, welfare, and dignity of every Filipino working abroad.</p>
            <div class="hero-buttons">
                <a href="/armas/pages/login.php" class="btn btn-gold btn-lg">Login</a>
                <a href="/armas/pages/register.php" class="btn btn-outline-white btn-lg">Register as OFW</a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="section" id="about">
        <div class="section-container">
            <div class="about-grid">
                <div class="about-image">
                    <img src="/armas/assets/img/armas.jpg" alt="ARMAS Mission" style="width:100%;border-radius:12px;">
                </div>
                <div class="about-content">
                    <h3>About ARMAS</h3>
                    <p>The Assistance and Repatriation Management and Action System (ARMAS) is a government initiative designed to provide comprehensive support services for Overseas Filipino Workers (OFWs).</p>
                    <p>Our platform streamlines the repatriation process, provides access to legal assistance, medical support, and ensures that every Filipino worker abroad has access to essential services and protection.</p>
                    <ul class="about-features">
                        <li>Emergency Repatriation Assistance</li>
                        <li>Legal Support and Counseling</li>
                        <li>Medical Support Services</li>
                        <li>Financial Aid Programs</li>
                        <li>Psychosocial Support</li>
                        <li>Documentation Assistance</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="section section-dark" id="services">
        <div class="section-container">
            <div class="section-header">
                <h2>Our Services</h2>
                <p>Comprehensive support services designed to protect and assist Filipino workers worldwide</p>
            </div>

            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon">✈️</div>
                    <h3>Emergency Repatriation</h3>
                    <p>Rapid assistance for OFWs in crisis situations, including emergency flights home, airport assistance, and coordination with foreign authorities.</p>
                </div>

                <div class="service-card">
                    <div class="service-icon">⚖️</div>
                    <h3>Legal Assistance</h3>
                    <p>Access to legal counsel for labor disputes, contract issues, and protection against exploitation. Free legal consultation services.</p>
                </div>

                <div class="service-card">
                    <div class="service-icon">🏥</div>
                    <h3>Medical Support</h3>
                    <p>Emergency medical assistance, hospitalization coordination, and connection to healthcare providers in host countries.</p>
                </div>

                <div class="service-card">
                    <div class="service-icon">💰</div>
                    <h3>Financial Aid</h3>
                    <p>Emergency loans, salary claims assistance, and access to government financial programs for distressed OFWs.</p>
                </div>

                <div class="service-card">
                    <div class="service-icon">🧠</div>
                    <h3>Psychosocial Support</h3>
                    <p>Counseling services for OFWs facing emotional distress, trauma, or difficult situations abroad.</p>
                </div>

                <div class="service-card">
                    <div class="service-icon">📄</div>
                    <h3>Documentation Assistance</h3>
                    <p>Help with passport renewal, OWWA membership, and other essential document processing requirements.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="section contact-section" id="contact">
        <div class="section-container">
            <div class="section-header">
                <h2>Contact Us</h2>
                <p>Get in touch with our support team for assistance</p>
            </div>

            <div class="contact-grid">
                <div class="contact-info">
                    <h3>We're Here to Help</h3>
                    <p>Reach out to us for any inquiries or assistance. Our dedicated team is available 24/7 to support Filipino workers abroad.</p>

                    <ul class="contact-details">
                        <li>
                            <span class="icon">📍</span>
                            <span>ARMAS Main Office, Manila, Philippines</span>
                        </li>
                        <li>
                            <span class="icon">📞</span>
                            <span>+63 2 8888 8888</span>
                        </li>
                        <li>
                            <span class="icon">📧</span>
                            <span>support@armas.gov.ph</span>
                        </li>
                        <li>
                            <span class="icon">🌐</span>
                            <span>www.armas.gov.ph</span>
                        </li>
                    </ul>
                </div>

                <div class="contact-form">
                    <h3>Send us a Message</h3>
                    <form>
                        <div class="form-group">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" placeholder="Your Name">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" placeholder="your@email.com">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Message</label>
                            <textarea class="form-control" rows="4" placeholder="How can we help you?"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Terms Section -->
    <section class="section terms-section" id="terms">
        <div class="section-container">
            <div class="section-header">
                <h2>Terms and Conditions</h2>
                <p>Please read our terms and conditions carefully</p>
            </div>

            <div class="terms-container">
                <div class="accordion">
                    <div class="accordion-item">
                        <div class="accordion-header">Terms of Service</div>
                        <div class="accordion-content">
                            <div class="accordion-content-inner">
                                <p>By accessing and using ARMAS, you agree to be bound by these terms. The service is provided for legitimate assistance purposes only. Users must provide accurate information and maintain the confidentiality of their account credentials.</p>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <div class="accordion-header">Privacy Policy</div>
                        <div class="accordion-content">
                            <div class="accordion-content-inner">
                                <p>ARMAS is committed to protecting your privacy. Personal information collected is used solely for assistance purposes and will not be shared with unauthorized parties. We comply with data protection regulations.</p>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <div class="accordion-header">User Responsibilities</div>
                        <div class="accordion-content">
                            <div class="accordion-content-inner">
                                <p>Users are responsible for maintaining the confidentiality of their login credentials and for all activities under their account. Any suspected unauthorized access should be reported immediately.</p>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <div class="accordion-header">Service Limitations</div>
                        <div class="accordion-content">
                            <div class="accordion-content-inner">
                                <p>ARMAS provides assistance within the scope of available resources. Response times may vary based on the nature of the request and availability of personnel. The system is not intended for emergency situations requiring immediate response.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-brand">
                <img src="/armas/assets/img/armas.jpg" alt="ARMAS Shield" class="footer-logo">
                <div>
                    <span class="logo-text">ARMAS</span>
                    <p class="footer-tagline">Protecting Every Filipino, Every Mile Away</p>
                </div>
            </div>

            <div class="footer-links">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="#home">Home</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="#services">Services</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </div>

            <div class="footer-links">
                <h4>Legal</h4>
                <ul>
                    <li><a href="#terms">Terms and Conditions</a></li>
                    <li><a href="#terms">Privacy Policy</a></li>
                </ul>
            </div>

            <div class="footer-contact">
                <h4>Contact Us</h4>
                <p>ARMAS Help Desk</p>
                <p>support@armas.gov.ph</p>
                <p>+63 2 8888 8888</p>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2025 ARMAS — Assistance and Repatriation Management and Action System. All rights reserved.</p>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="/armas/assets/js/main.js"></script>
    <script src="/armas/assets/js/validation.js"></script>

</body>

</html>