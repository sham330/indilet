<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>What We Offer - IndiLet</title>
    <link rel="icon" type="image/x-icon" href="favicon.png">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="./css/header.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            background: #fafafa;
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
            max-width: 800px;
            margin: 0 auto 48px;
            padding: 0 20px;
            line-height: 1.7;
        }

        /* What We Offer Section */
        .what-we-offer {
            padding: 80px 0;
            background: #fafafa;
            margin-top:80px;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            max-width: 1180px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .service-box {
            position: relative;
            height: 280px;
            border-radius: 20px;
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .service-box:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }

        .service-box-bg {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            transition: transform 0.3s ease;
        }

        .service-box:hover .service-box-bg {
            transform: scale(1.05);
        }

        .service-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px;
            transition: background-color 0.3s ease;
        }

        .service-box:hover .service-overlay {
            background: rgba(0, 0, 0, 0.3);
        }

        .service-name {
            color: white;
            font-size: 1.4rem;
            font-weight: 700;
            text-align: center;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            line-height: 1.3;
            transition: transform 0.3s ease;
        }

        .service-box:hover .service-name {
            transform: scale(1.05);
        }

        /* Service Details Section */
        .service-details {
            padding: 60px 0;
            background: #fff;
        }

        .details-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .detail-item {
            margin-bottom: 40px;
            padding: 30px;
            border-radius: 15px;
            background: #f8f9fa;
            border-left: 5px solid #ff7300;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .detail-item:hover {
            transform: translateX(5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }

        .detail-item h3 {
            color: #222;
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .detail-item h3 i {
            color: #ff7300;
            font-size: 1.1rem;
        }

        .detail-item p {
            color: #555;
            line-height: 1.6;
            font-size: 1rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .services-grid {
                grid-template-columns: 1fr;
                gap: 20px;
                padding: 0 15px;
            }

            .service-box {
                height: 220px;
                min-width: auto;
            }

            .service-name {
                font-size: 1.2rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .section-subtitle {
                font-size: 1rem;
                padding: 0 15px;
            }

            .detail-item {
                padding: 20px;
                margin-bottom: 25px;
            }

            .detail-item h3 {
                font-size: 1.1rem;
            }
        }

        @media (max-width: 480px) {
            .services-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .service-box {
                height: 200px;
            }

            .service-name {
                font-size: 1.1rem;
                padding: 0 10px;
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
    <!-- What We Offer Section -->
    <section class="what-we-offer">
        <div class="container">
            <h1 class="section-title">What We Offer</h1>
            <p class="section-subtitle">
                At IndiLet, our mission is to make renting simple, transparent, and stress-free—so employees can focus on settling into their new home and role with ease. We offer a full range of relocation and rental services designed to support both corporate partners and property owners, while ensuring a smooth and personalized experience for every tenant.
            </p>
            
            <div class="services-grid">
                <!-- Corporate Partnerships -->
                <div class="service-box" data-aos="fade-up" data-aos-duration="800" data-aos-delay="100">
                    <div class="service-box-bg" style="background-image: url('assets/service1.webp');"></div>
                    <div class="service-overlay">
                        <h3 class="service-name">Corporate Partnerships</h3>
                    </div>
                </div>

                <!-- Property Onboarding -->
                <div class="service-box" data-aos="fade-up" data-aos-duration="800" data-aos-delay="200">
                    <div class="service-box-bg" style="background-image: url('assets/service2.webp');"></div>
                    <div class="service-overlay">
                        <h3 class="service-name">Property Onboarding</h3>
                    </div>
                </div>

                <!-- End-to-End Relocation Management -->
                <div class="service-box" data-aos="fade-up" data-aos-duration="800" data-aos-delay="300">
                    <div class="service-box-bg" style="background-image: url('assets/service3.webp');"></div>
                    <div class="service-overlay">
                        <h3 class="service-name">End-to-End Relocation Management</h3>
                    </div>
                </div>

                <!-- Temporary Accommodation -->
                <div class="service-box" data-aos="fade-up" data-aos-duration="800" data-aos-delay="400">
                    <div class="service-box-bg" style="background-image: url('assets/service4.webp');"></div>
                    <div class="service-overlay">
                        <h3 class="service-name">Temporary Accommodation</h3>
                    </div>
                </div>

                <!-- Employee-Centric Add-On Services -->
                <div class="service-box" data-aos="fade-up" data-aos-duration="800" data-aos-delay="500">
                    <div class="service-box-bg" style="background-image: url('assets/service5.webp');"></div>
                    <div class="service-overlay">
                        <h3 class="service-name">Employee-Centric Add-On Services</h3>
                    </div>
                </div>

                <!-- On-Demand Assistance -->
                <div class="service-box" data-aos="fade-up" data-aos-duration="800" data-aos-delay="600">
                    <div class="service-box-bg" style="background-image: url('assets/service6.webp');"></div>
                    <div class="service-overlay">
                        <h3 class="service-name">On-Demand Assistance</h3>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Service Details Section -->
    <section class="service-details">
        <div class="details-container">
            <div class="detail-item" data-aos="fade-up" data-aos-duration="600">
                <h3><i class="fas fa-handshake"></i>Corporate Partnerships</h3>
                <p>Collaborating with companies to support employee relocation and long-term housing needs.</p>
            </div>

            <div class="detail-item" data-aos="fade-up" data-aos-duration="600" data-aos-delay="100">
                <h3><i class="fas fa-home"></i>Property Onboarding</h3>
                <p>Partnering with homeowners to list and manage their rental properties through our platform.</p>
            </div>

            <div class="detail-item" data-aos="fade-up" data-aos-duration="600" data-aos-delay="200">
                <h3><i class="fas fa-route"></i>End-to-End Relocation Management</h3>
                <p>Managing the entire process—from move-in to move-out—with a focus on comfort, speed, and efficiency.</p>
            </div>

            <div class="detail-item" data-aos="fade-up" data-aos-duration="600" data-aos-delay="300">
                <h3><i class="fas fa-bed"></i>Temporary Accommodation</h3>
                <p>Providing short-term stays for employees upon arrival, ensuring a smooth transition into the city.</p>
            </div>

            <div class="detail-item" data-aos="fade-up" data-aos-duration="600" data-aos-delay="400">
                <h3><i class="fas fa-concierge-bell"></i>Employee-Centric Add-On Services</h3>
                <p>Offering curated services like cleaning, internet setup, utility management, and more—tailored to employee needs.</p>
            </div>

            <div class="detail-item" data-aos="fade-up" data-aos-duration="600" data-aos-delay="500">
                <h3><i class="fas fa-headset"></i>On-Demand Assistance</h3>
                <p>Additional services available upon request to ensure a fully customized and responsive experience.</p>
            </div>
        </div>
    </section>
 <!-- CTA Section -->
    <section class="cta-section">
        <div class="cta-content" data-aos="fade-up" data-aos-duration="1000">
            <h2>Ready to Transform Your Rental Experience?</h2>
            <p>Join us in revolutionizing how India rents, explores, and settles</p>
            <a href="listing.php" class="cta-button" data-aos="zoom-in" data-aos-duration="800" data-aos-delay="200">
                <i class="fas fa-arrow-right"></i> Get Started Today
            </a>
        </div>
    </section>
            <?php include('./components/footer.php'); ?>

    <!-- AOS JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true,
            offset: 120,
            easing: 'ease-out-quart'
        });
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

    </script>
</body>
</html>
