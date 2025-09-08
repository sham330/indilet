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
    <title>Indilet - Find Your Perfect Rental Home</title>
<link rel="icon" type="image/x-icon" href="favicon.png">

    <link rel="stylesheet" href="./css/header.css">

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
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

        /* Contact Form Styles */
        body {
            font-family: 'Roboto', sans-serif;
        }
        
        .contact-section {
            padding: 80px 0;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        }
        
        .contact-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 1.5rem;
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
            margin-bottom: 48px;
            font-weight: 400;
        }
        
        .contact-form {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            border: 1px solid #f0f0f0;
            margin-bottom: 2rem;
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
        
        /* Full Width Contact Info Section */
        .contact-info-section {
            background: linear-gradient(135deg, #ff7300 0%, #e6670a 100%);
            padding: 60px 0;
        }
        
        .contact-info-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .contact-info {
            text-align: center;
            color: white;
        }
        
        .contact-info h3 {
            color: white;
            margin-bottom: 40px;
            font-size: 2.2rem;
            font-weight: 700;
            letter-spacing: 0.02em;
        }
        
        .contact-items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
            max-width: 900px;
            margin: 0 auto;
        }
        
        .contact-item {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            color: white;
            font-size: 1.2rem;
            font-weight: 500;
            padding: 25px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }
        
        .contact-item:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        .contact-item i {
            color: white;
            font-size: 1.8rem;
            width: 30px;
            text-align: center;
        }
        
        .contact-item a {
            color: white;
            text-decoration: none;
            font-weight: 600;
        }
        
        .contact-item a:hover {
            color: #fff;
            text-decoration: underline;
        }
        
        .contact-item span {
            font-weight: 600;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .contact-form {
                padding: 30px 20px;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .contact-info h3 {
                font-size: 1.8rem;
            }
            
            .contact-items-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .contact-item {
                padding: 20px;
                font-size: 1.1rem;
            }
            
            .contact-info-container {
                padding: 0 1rem;
            }
        }
        
        @media (max-width: 480px) {
            .contact-section {
                padding: 60px 0;
            }
            
            .contact-container {
                padding: 0 1rem;
            }
            
            .contact-item {
                flex-direction: column;
                text-align: center;
                gap: 10px;
                padding: 20px 15px;
            }
            
            .contact-item i {
                margin-bottom: 5px;
            }
            .cta-content h2 {
                font-size: 2rem;
            }
        }

         /* CTA Section */
        .cta-section {
            padding: 80px 0;
            background: linear-gradient(135deg, #ff7300, #ff8f33);
            color: white;
            text-align: center;
        }

        .cta-content {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        .cta-content h2 {
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
            font-weight: 700;
        }

        .cta-content p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.95;
        }

        .cta-button {
            display: inline-block;
            background: white;
            color: #ff7300;
            padding: 15px 40px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
        }


    </style>
</head>
<body>
    <div id="loader">
        <img src="assets/loader.gif" alt="Loading...">
        <p>Finding Homes...</p>
    </div>
    
    <a href="https://wa.me/+918522863853" target="_blank" class="whatsapp-float">
        <i class="fab fa-whatsapp"></i>
    </a>
    
    <?php include('./components/header.php'); ?>
    
    <?php include("./components/hero.php"); ?>
    
    <?php include("./components/who-we-are.php"); ?>
    
    <!-- Contact Section -->
    <section class="contact-section" id="contact">
        <div class="contact-container">
            <h2 class="section-title">Contact Us</h2>
            <p class="section-subtitle">Get in touch with us for any queries or assistance. We're here to help!</p>
            
            <!-- Alert Messages -->
            <?php if (!empty($message)): ?>
                <div class="alert <?php echo $messageType === 'success' ? 'alert-success' : 'alert-error'; ?>" id="alertMessage">
                    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                    <span><?php echo htmlspecialchars($message); ?></span>
                    <button type="button" class="close-btn" onclick="closeAlert()">&times;</button>
                </div>
            <?php endif; ?>
            
            <form class="contact-form" method="POST" action="" id="contactForm">
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
                    <textarea id="message" name="message" placeholder="Tell us how we can help you..." required></textarea>
                </div>
                
                <button type="submit" class="submit-btn" id="submitBtn">
                    <i class="fas fa-paper-plane"></i>
                    Send Message
                </button>
            </form>
        </div>
    </section>
    <!-- CTA Section -->
    <section class="cta-section">
        <div class="cta-content" data-aos="fade-up" data-aos-duration="1000">
            <h2>Ready to Transform Your Rental Experience?</h2>
            <p>Join us in revolutionizing how India rents, explores, and settles</p>
            <a href="listings" class="cta-button" data-aos="zoom-in" data-aos-duration="800" data-aos-delay="200">
                <i class="fas fa-arrow-right"></i> Get Started Today
            </a>
        </div>
    </section>
    
   <?php include("./components/footer.php"); ?>
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
            // If we have a status parameter, scroll to contact section
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('status')) {
                document.getElementById('contact').scrollIntoView({ behavior: 'smooth' });
                
                // Clean the URL after a short delay (optional - removes the parameters from URL)
                setTimeout(() => {
                    const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + '#contact';
                    window.history.replaceState({}, document.title, cleanUrl);
                }, 1000);
            }
        });

        // Prevent accidental form resubmission by clearing form after successful submission
        <?php if ($messageType === 'success'): ?>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('contactForm').reset();
        });
        <?php endif; ?>
    </script>
</body>
</html>