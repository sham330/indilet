<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentEasy - Professional Footer</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
        }

        .footer {
            position: relative;
            padding: 60px 0 0;
            width: 100%;
            min-height: 50vh;
            overflow: hidden;
        }

        .video-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
        }

        .video-background video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .footer-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                135deg,
                rgba(0, 0, 0, 0.85) 0%,
                rgba(0, 0, 0, 0.8) 100%
            );
            z-index: -1;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            position: relative;
            z-index: 1;
        }

        .footer-content {
            display: grid;
            grid-template-columns: 1.8fr 1.5fr 1fr;
            gap: 50px;
            margin-bottom: 50px;
            align-items: start;
        }

        .footer-section h3 {
            color: #ffffff;
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 25px;
            position: relative;
        }

        .footer-section h3::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 35px;
            height: 2px;
            background: #ff6600;
            border-radius: 2px;
        }

        /* Company Info */
        .company-info p {
            color: #e0e0e0;
            font-size: 0.9rem;
            margin-bottom: 15px;
            line-height: 1.6;
            font-weight: 300;
        }

        .company-info .tagline {
            color: #ff9900;
            font-weight: 500;
            font-size: 0.95rem;
            margin-top: 20px;
        }

        /* City Links */
        .city-links {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
        }

        .city-link {
            display: flex;
            align-items: center;
            padding: 16px 18px;
            border-radius: 50px;
            text-decoration: none;
            color: #ffffff;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }

        .city-link:hover {
            color: #ff9900;
            transform: translateX(8px);
        }

        .city-icon {
            width: 32px;
            height: 32px;
            margin-right: 15px;
            background: #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            flex-shrink: 0;
            border: 2px solid #f0f0f0;
        }

        .city-icon img {
            width: 20px;
            height: 20px;
            object-fit: cover;
        }

        .city-link:hover .city-icon {
            background: #ffffff;
            transform: scale(1.1);
            border-color: #e0e0e0;
        }

        .city-name {
            font-size: 1rem;
            font-weight: 500;
        }

        /* Contact Info */
        .contact-info .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 18px;
            color: #e0e0e0;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .contact-item:hover {
            color: #ff9900;
            transform: translateX(6px);
        }

        .contact-icon {
            width: 30px;
            height: 30px;
            margin-right: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ff6600;
            font-size: 14px;
            flex-shrink: 0;
            transition: all 0.3s ease;
        }

        .contact-item:hover .contact-icon {
            color: #ff9900;
            transform: scale(1.05);
        }

        /* Footer Bottom */
        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.15);
            padding: 30px 0;
            text-align: center;
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .social-link {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 16px;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .social-link:hover {
            background: #ff6600;
            color: #ffffff;
            border-color: #ff9900;
            transform: translateY(-3px);
        }

        .footer-bottom p {
            color: #cccccc;
            font-size: 0.85rem;
            font-weight: 300;
            line-height: 1.5;
        }

        .footer-bottom a {
            color: #ff9900;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-bottom a:hover {
            color: #ffffff;
        }

        /* Popup Styles */
        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            animation: fadeIn 0.3s ease;
        }

        .popup-content {
            background: #ffffff;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.3s ease;
        }

        .popup-content h3 {
            color: #333;
            font-size: 1.4rem;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .popup-content p {
            color: #666;
            font-size: 1rem;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .popup-close {
            background: #ff6600;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .popup-close:hover {
            background: #ff9900;
            transform: translateY(-2px);
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { 
                opacity: 0;
                transform: translateY(30px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .footer-content {
                grid-template-columns: 1fr 1fr;
                gap: 40px;
            }

            .footer-section:first-child {
                grid-column: 1 / -1;
                text-align: center;
                margin-bottom: 20px;
            }

            .footer-section h3::after {
                left: 50%;
                transform: translateX(-50%);
            }
        }

        @media (max-width: 768px) {
            .footer {
                padding: 40px 0 0;
                min-height: 40vh;
            }

            .footer-content {
                grid-template-columns: 1fr;
                gap: 35px;
                text-align: center;
            }

            .city-links {
                grid-template-columns: 1fr;
                max-width: 300px;
                margin: 0 auto;
            }

            .footer-section h3 {
                font-size: 1.2rem;
            }

            .footer-section h3::after {
                left: 50%;
                transform: translateX(-50%);
            }

            .city-link {
                padding: 14px 16px;
            }

            .social-links {
                gap: 12px;
            }

            .social-link {
                width: 38px;
                height: 38px;
                font-size: 14px;
            }

            .popup-content {
                padding: 25px;
            }

            .popup-content h3 {
                font-size: 1.2rem;
            }

            .popup-content p {
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0 15px;
            }

            .footer {
                min-height: 35vh;
            }

            .city-link {
                padding: 12px 14px;
            }

            .city-icon {
                width: 28px;
                height: 28px;
                margin-right: 12px;
            }

            .city-icon img {
                width: 18px;
                height: 18px;
            }

            .city-name {
                font-size: 0.9rem;
            }

            .footer-section h3 {
                font-size: 1.1rem;
            }

            .contact-icon {
                width: 26px;
                height: 26px;
                font-size: 12px;
            }

            .contact-item {
                font-size: 0.85rem;
            }

            .popup-content {
                padding: 20px;
                max-width: 350px;
            }

            .popup-content h3 {
                font-size: 1.1rem;
            }

            .popup-content p {
                font-size: 0.85rem;
            }

            .popup-close {
                padding: 10px 25px;
                font-size: 0.9rem;
            }
        }

        /* Simple animations */
        .fade-up {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }

        .fade-up.visible {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <footer class="footer">
        <!-- Video Background -->
        <div class="video-background">
            <video autoplay muted loop>
                <source src="hero.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>
        
        <!-- Overlay -->
        <div class="footer-overlay"></div>

        <div class="container">
            <div class="footer-content">
                <!-- Company Info -->
                <div class="footer-section fade-up" data-aos="fade-right">
                    <h3>Indilet</h3>
                    <div class="company-info">
                        <p>At IndiLet, we simplify the relocation journey for professionals moving to a new city for work.</p>
                        <p>By partnering with leading organizations, we provide seamless housing solutions that ease the stress of settling in and help new employees feel at home faster.</p>
                        <p class="tagline">Your Dream Rental, Just a Click Away!</p>
                    </div>
                </div>

                <!-- City Links -->
                <div class="footer-section fade-up" data-aos="fade-up" data-aos-delay="200">
                    <h3>Our Locations</h3>
                    <div class="city-links">
                        <a href="listing.php" class="city-link" data-city="hyderabad" data-aos="fade-up" data-aos-delay="200">
                            <div class="city-icon">
                                <img src="assets/charminar.png" alt="Hyderabad">
                            </div>
                            <span class="city-name">Hyderabad</span>
                        </a>
                        <a href="#" class="city-link" data-city="mumbai" data-aos="fade-up" data-aos-delay="300">
                            <div class="city-icon">
                                <img src="assets/gate-of-india.png" alt="Mumbai">
                            </div>
                            <span class="city-name">Mumbai</span>
                        </a>
                        <a href="#" class="city-link" data-city="chennai" data-aos="fade-up" data-aos-delay="400">
                            <div class="city-icon">
                                <img src="assets/monument.png" alt="Chennai">
                            </div>
                            <span class="city-name">Chennai</span>
                        </a>
                        <a href="#" class="city-link" data-city="bangalore" data-aos="fade-up" data-aos-delay="500">
                            <div class="city-icon">
                                <img src="assets/bangalore.png" alt="Bangalore">
                            </div>
                            <span class="city-name">Bangalore</span>
                        </a>
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="footer-section fade-up" data-aos="fade-left" data-aos-delay="300">
                    <h3>Get in Touch</h3>
                    <div class="contact-info">
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <span>+91 8522863853</span>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <span>contact@indilet.com</span>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <span>Raja Rajeswari Nagar, Kondapur, Hyderabad, Telangana 500084</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="footer-bottom fade-up" data-aos="fade-up" data-aos-delay="700">
                <div class="social-links">
                    <a href="#" class="social-link" title="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="social-link" title="Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="social-link" title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="social-link" title="LinkedIn">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                    <a href="#" class="social-link" title="YouTube">
                        <i class="fab fa-youtube"></i>
                    </a>
                </div>
                <p>&copy; 2025 RentEasy. All rights reserved. | <a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a></p>
            </div>
        </div>
    </footer>

    <!-- Popup Modal -->
    <div class="popup-overlay" id="popup">
        <div class="popup-content">
            <h3>🎉 Coming Soon!</h3>
            <p>Thanks for your enthusiasm! We will arrive there shortly.</p>
            <button class="popup-close" onclick="closePopup()">Got it!</button>
        </div>
    </div>

    <!-- AOS Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-out',
            once: true,
            offset: 100
        });

        // Location link interactions
        const locationLinks = document.querySelectorAll('.city-link');
        
        locationLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                const cityName = this.getAttribute('data-city');
                
                // Check if it's Hyderabad - allow normal navigation
                if (cityName === 'hyderabad') {
                    // Allow navigation to Hyderabad page
                    window.location.href = this.href;
                    return;
                }
                
                // For all other cities, show popup
                showPopup();
                
                // Simple visual feedback
                this.style.transform = 'translateX(8px) scale(0.98)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 200);
            });
        });

        // Popup functions
        function showPopup() {
            const popup = document.getElementById('popup');
            popup.style.display = 'flex';
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
        }

        function closePopup() {
            const popup = document.getElementById('popup');
            popup.style.display = 'none';
            document.body.style.overflow = 'auto'; // Restore scrolling
        }

        // Close popup when clicking outside the content
        document.getElementById('popup').addEventListener('click', function(e) {
            if (e.target === this) {
                closePopup();
            }
        });

        // Close popup with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closePopup();
            }
        });

        // Video background fallback
        const video = document.querySelector('.video-background video');
        if (video) {
            video.addEventListener('error', function() {
                const videoContainer = document.querySelector('.video-background');
                videoContainer.style.background = 'linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 50%, #1a1a1a 100%)';
            });
        }

        // Simple fade-up animation fallback for older browsers
        const observeElements = () => {
            const elements = document.querySelectorAll('.fade-up');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, { threshold: 0.1 });

            elements.forEach(el => observer.observe(el));
        };

        // Initialize on load
        document.addEventListener('DOMContentLoaded', observeElements);
    </script>
</body>
</html>
