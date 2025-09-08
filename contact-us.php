<?php
// Include database connection for contact form
require_once 'connection.php';

// Initialize variables
$message = '';
$messageType = '';

// Check if we have URL parameters for success/error messages
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'success') {
        $message = "Thank you for reaching out! We have received your message and will get back to you within 24 hours.";
        $messageType = "success";
    } elseif ($_GET['status'] === 'error' && isset($_GET['msg'])) {
        $message = urldecode($_GET['msg']);
        $messageType = "error";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get form data and sanitize
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phonenumber = trim($_POST['phone']);
        $messageText = trim($_POST['message']);
        
        // Validation
        if (empty($name) || empty($email) || empty($phonenumber) || empty($messageText)) {
            throw new Exception("All fields are required!");
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Please enter a valid email address!");
        }
        
        if (!preg_match("/^[0-9]{10,15}$/", $phonenumber)) {
            throw new Exception("Please enter a valid phone number!");
        }
        
        // Prepare and execute the insert statement
        $sql = "INSERT INTO messages (name, email, phonenumber, message, status, created_at) VALUES (?, ?, ?, ?, 'pending', CURRENT_TIMESTAMP)";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("ssss", $name, $email, $phonenumber, $messageText);
        
        if ($stmt->execute()) {
            // Success - redirect to prevent form resubmission
            $stmt->close();
            header("Location: " . $_SERVER['PHP_SELF'] . "?status=success#contact");
            exit();
        } else {
            throw new Exception("Error submitting message: " . $stmt->error);
        }
        
    } catch (Exception $e) {
        // Error - redirect with error message
        $errorMsg = urlencode($e->getMessage());
        header("Location: " . $_SERVER['PHP_SELF'] . "?status=error&msg=" . $errorMsg . "#contact");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - IndiLet</title>
    <link rel="stylesheet" href="./css/header.css">
    <link rel="icon" type="image/x-icon" href="favicon.png">

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            line-height: 1.6;
            color: #333;
            background: #fafafa;
        }

        .whatsapp-float {
    position: fixed;
    width: 60px;
    height: 60px;
    bottom: 20px;
    right: 20px;
    background-color: #25d366;
    color: white;
    border-radius: 50%;
    text-align: center;
    font-size: 30px;
    box-shadow: 2px 2px 5px rgba(0,0,0,0.3);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.3s ease;
    animation: heartbeat 1.5s ease-in-out infinite;
}

.whatsapp-float:hover {
    transform: scale(1.1);
    cursor: pointer;
    animation-play-state: paused; /* Pause animation on hover */
}

/* Heartbeat animation */
@keyframes heartbeat {
    0% {
        transform: scale(1);
    }
    14% {
        transform: scale(1.1);
    }
    28% {
        transform: scale(1);
    }
    42% {
        transform: scale(1.1);
    }
    70% {
        transform: scale(1);
    }
    100% {
        transform: scale(1);
    }
}


        /* Contact Section Styles */
        .contact-section {
            padding: 80px 0;
            background: #fafafa;
            min-height: 100vh;
        }

        .contact-main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            color: #222;
            font-weight: 700;
            margin: 0 0 0.5rem 0;
            letter-spacing: 0.02em;
        }

        .section-title::after {
            content: '';
            display: block;
            margin: 18px auto;
            width: 64px;
            height: 4px;
            background: #ff7300;
            border-radius: 2px;
        }

        .section-subtitle {
            font-size: 1.1rem;
            color: #444;
            text-align: center;
            margin-bottom: 60px;
            font-weight: 400;
        }

        /* Main Contact Layout */
        .contact-layout {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 40px;
            align-items: start;
        }

        /* Contact Form */
        .contact-form-container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            border: 1px solid #f0f0f0;
        }

        .form-header {
            margin-bottom: 30px;
        }

        .form-header h3 {
            color: #222;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .form-header p {
            color: #666;
            font-size: 1rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 1rem;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: inherit;
            background: #fafafa;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #ff7300;
            background: white;
            box-shadow: 0 0 0 3px rgba(255, 115, 0, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .submit-btn {
            width: 100%;
            padding: 16px 24px;
            background: #ff7300;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 16px rgba(255, 115, 0, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .submit-btn:hover {
            background: #e6670a;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 115, 0, 0.3);
        }

        .submit-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* Contact Information Panel - Clean White Design */
        .contact-info-panel {
            background: white;
            padding: 40px 30px;
            border-radius: 20px;
            color: #333;
            position: sticky;
            top: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            border: 1px solid #f0f0f0;
        }

        .contact-info-header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f8f9fa;
        }

        .contact-info-header h3 {
            color: #222;
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .contact-info-header p {
            color: #666;
            font-size: 1rem;
        }

        .contact-info-items {
            space: 25px 0;
        }

        .contact-info-item {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 15px;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .contact-info-item:hover {
            background: #fff;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border-color: #ff7300;
        }

        .contact-info-item:last-child {
            margin-bottom: 0;
        }

        .contact-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #ff7300, #e6670a);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(255, 115, 0, 0.3);
        }

        .contact-icon i {
            color: white;
            font-size: 1.3rem;
        }

        .contact-details h4 {
            color: #222;
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .contact-details p {
            color: #666;
            font-size: 0.95rem;
            line-height: 1.4;
            margin: 0;
        }

        .contact-details a {
            color: #ff7300;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .contact-details a:hover {
            color: #e6670a;
            text-decoration: underline;
        }

        /* Alert Messages */
        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 2rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
        }

        .alert-error {
            background: #f8d7da;
            color: #842029;
            border: 1px solid #f5c2c7;
        }

        .alert i {
            font-size: 1.2rem;
        }

        .alert .close-btn {
            margin-left: auto;
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            color: inherit;
            opacity: 0.7;
        }

        .alert .close-btn:hover {
            opacity: 1;
        }

        /* Business Hours */
        .business-hours {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 15px;
            margin-top: 25px;
            border: 1px solid #e9ecef;
        }

        .business-hours h4 {
            color: #222;
            font-size: 1.1rem;
            margin-bottom: 15px;
            text-align: center;
            font-weight: 700;
        }

        .hours-list {
            list-style: none;
        }

        .hours-list li {
            display: flex;
            justify-content: space-between;
            color: #666;
            margin-bottom: 8px;
            font-size: 0.95rem;
            padding: 5px 0;
        }

        .hours-list li:last-child {
            margin-bottom: 0;
        }

        .hours-list li span:first-child {
            font-weight: 600;
            color: #333;
        }

        /* Quick Contact CTA */
        .quick-contact-cta {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            background: linear-gradient(135deg, #ff7300, #e6670a);
            border-radius: 15px;
            color: white;
        }

        .quick-contact-cta h4 {
            color: white;
            margin-bottom: 10px;
            font-size: 1.1rem;
        }

        .quick-contact-cta p {
            color: rgba(255,255,255,0.9);
            font-size: 0.9rem;
            margin-bottom: 15px;
        }

        .cta-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .cta-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.2);
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .cta-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }

        /* Responsive Design */
        @media (max-width: 968px) {
            .contact-layout {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .contact-info-panel {
                position: static;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .contact-section {
                padding: 60px 0;
            }

            .contact-main-container {
                padding: 0 1rem;
            }

            .contact-form-container {
                padding: 30px 20px;
            }

            .contact-info-panel {
                padding: 30px 20px;
            }

            .section-title {
                font-size: 2rem;
            }

            .contact-info-item {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }

            .contact-details h4 {
                margin-bottom: 8px;
            }

            .cta-buttons {
                flex-direction: column;
            }

            .cta-btn {
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .contact-main-container {
                padding: 0 0.5rem;
            }

            .contact-form-container {
                padding: 20px 15px;
            }

            .contact-info-panel {
                padding: 20px 15px;
            }

            .contact-info-item {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <a href="https://wa.me/+918522863853" target="_blank" class="whatsapp-float">
        <i class="fab fa-whatsapp"></i>
    </a>
    <div id="loader">
        <img src="assets/loader.gif" alt="Loading...">
        <p>Finding Homes...</p>
    </div>
    <?php include('./components/header.php'); ?>
    
    <!-- Contact Section -->
    <section class="contact-section" style="margin-top:80px;" id="contact">
        <div class="contact-main-container">
            <h2 class="section-title" data-aos="fade-up" data-aos-duration="800">Contact Us</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-duration="800" data-aos-delay="200">Get in touch with us for any queries or assistance. We're here to help you find your perfect home!</p>
            
            <!-- Alert Messages -->
            <?php if (!empty($message)): ?>
                <div class="alert <?php echo $messageType === 'success' ? 'alert-success' : 'alert-error'; ?>" id="alertMessage" data-aos="fade-down" data-aos-duration="600">
                    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                    <span><?php echo htmlspecialchars($message); ?></span>
                    <button type="button" class="close-btn" onclick="closeAlert()">&times;</button>
                </div>
            <?php endif; ?>
            
            <div class="contact-layout">
                <!-- Contact Form -->
                <div class="contact-form-container" data-aos="fade-right" data-aos-duration="1000" data-aos-delay="300">
                    <div class="form-header">
                        <h3>Send Us a Message</h3>
                        <p>Fill out the form below and we'll get back to you within 24 hours.</p>
                    </div>
                    
                    <form method="POST" action="" id="contactForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Full Name *</label>
                                <input type="text" id="name" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone Number *</label>
                                <input type="tel" id="phone" name="phone" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Your Message *</label>
                            <textarea id="message" name="message" placeholder="Tell us how we can help you find your perfect rental home..." required></textarea>
                        </div>
                        
                        <button type="submit" class="submit-btn" id="submitBtn">
                            <i class="fas fa-paper-plane"></i>
                            Send Message
                        </button>
                    </form>
                </div>

                <!-- Contact Information Panel -->
                <div class="contact-info-panel" data-aos="fade-left" data-aos-duration="1000" data-aos-delay="400">
                    <div class="contact-info-header">
                        <h3>Get in Touch</h3>
                        <p>Ready to find your perfect rental? Contact us today!</p>
                    </div>
                    <div class="contact-info-item" data-aos="fade-up" data-aos-duration="600" data-aos-delay="700">
                            <div class="contact-icon" style="background: green">
                                <i class="fab fa-whatsapp"></i>
                            </div>
                            <div class="contact-details">
                                <h4>WhatsApp</h4>
                                <p><a href="https://wa.me/+918522863853" target="_blank" style="color:green;">+91 8522863853</a></p>
                                <p>Quick Response Guaranteed</p>
                            </div>
                        </div>

                    <div class="contact-info-items">
                        <div class="contact-info-item" data-aos="fade-up" data-aos-duration="600" data-aos-delay="500">
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="contact-details">
                                <h4>Call Us</h4>
                                <p><a href="tel:+918522863853">+91 8522863853</a></p>
                            </div>
                        </div>

                        <div class="contact-info-item" data-aos="fade-up" data-aos-duration="600" data-aos-delay="600">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-details">
                                <h4>Email Us</h4>
                                <p><a href="mailto:contact@indilet.com">contact@indilet.com</a></p>
                            </div>
                        </div>

                        
                        <div class="contact-info-item" data-aos="fade-up" data-aos-duration="600" data-aos-delay="800">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="contact-details">
                                <h4>Visit Us</h4>
                                <p>Vasavi nilayam,Plot No 1409,</p>
                                <p>Raja Rajeswari Nagar, Kondapur, Hyderabad, Telangana 500084</p>
                            </div>
                        </div>
                    </div>

                   
                   
                </div>
            </div>
        </div>
    </section>
    
    <?php include("./components/footer.php"); ?>

    <!-- AOS JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
         // Hide loader after page fully loads
        window.addEventListener("load", () => {
            const loader = document.getElementById("loader");

            // Fade out smoothly
            loader.style.transition = "opacity 0.8s ease";
            loader.style.opacity = "0";

            setTimeout(() => {
                loader.style.display = "none";
            }, 800); // match transition duration
        });
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true,
            offset: 120,
            easing: 'ease-out-quart'
        });

        // Form validation
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            const phone = document.getElementById('phone').value.trim();
            const email = document.getElementById('email').value.trim();
            const message = document.getElementById('message').value.trim();
            
            // Basic validation
            if (!name || !phone || !email || !message) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }
            
            // Phone validation
            if (!/^[0-9]{10,15}$/.test(phone)) {
                e.preventDefault();
                alert('Please enter a valid phone number (10-15 digits only).');
                return false;
            }
            
            // Email validation
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                e.preventDefault();
                alert('Please enter a valid email address.');
                return false;
            }
            
            // Disable submit button to prevent double submission
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
        });
        
        // Close alert function
        function closeAlert() {
            const alert = document.getElementById('alertMessage');
            if (alert) {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.3s ease';
                setTimeout(() => {
                    alert.remove();
                }, 300);
            }
        }
        
        // Auto-hide success message after 8 seconds
        const successAlert = document.querySelector('.alert-success');
        if (successAlert) {
            setTimeout(() => {
                closeAlert();
            }, 8000);
        }

        // Scroll to contact form if there's a message and clean URL
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('status')) {
                document.getElementById('contact').scrollIntoView({ behavior: 'smooth' });
                
                setTimeout(() => {
                    const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + '#contact';
                    window.history.replaceState({}, document.title, cleanUrl);
                }, 1000);
            }
        });

        // Clear form after successful submission
        <?php if ($messageType === 'success'): ?>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('contactForm').reset();
        });
        <?php endif; ?>
    </script>
</body>
</html>
