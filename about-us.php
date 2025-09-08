<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - IndiLet</title>
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
        }

        /* Who We Are Section Styles */
        .who-we-are-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
            max-width: 1180px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        .who-we-are-image {
            position: relative;
            overflow: hidden;
            border-radius: 20px;
            box-shadow: 0 12px 32px rgba(0,0,0,0.1);
        }

        .who-we-are-image img {
            width: 100%;
            height: auto;
            display: block;
            border-radius: 20px;
            transition: transform 0.3s ease;
        }

        .who-we-are-image:hover img {
            transform: scale(1.05);
        }

        .who-we-are-content {
            padding: 20px 0;
        }

        .who-we-are-content h2 {
            font-size: 2.2rem;
            color: #222;
            font-weight: 700;
            margin-bottom: 24px;
            position: relative;
        }

        .who-we-are-content h2::after {
            content: '';
            display: block;
            width: 50px;
            height: 4px;
            background: #ff7300;
            border-radius: 2px;
            margin-top: 12px;
        }

        .who-we-are-content p {
            font-size: 1.1rem;
            line-height: 1.7;
            color: #444;
            margin-bottom: 20px;
        }

        .view-more-btn {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background: #ff7300;
            color: white;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.05rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 16px rgba(255, 115, 0, 0.2);
            margin-top: 8px;
        }

        .view-more-btn:hover {
            background: #e6670a;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 115, 0, 0.3);
            gap: 16px;
        }

        .view-more-btn i {
            font-size: 1.1em;
            transition: transform 0.3s ease;
        }

        .view-more-btn:hover i {
            transform: translateX(4px);
        }

        /* Team Stats Section */
        .team-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
            max-width: 600px;
            margin: 40px 0;
        }

        .stat-card {
            background: linear-gradient(135deg, #ff7300, #ff8f33);
            color: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            transform: scale(1);
            transition: transform 0.3s ease;
            box-shadow: 0 4px 16px rgba(255, 115, 0, 0.2);
        }

        .stat-card:hover {
            transform: scale(1.05);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1rem;
            opacity: 0.9;
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

        /* Responsive Design */
        @media (max-width: 968px) {
            .who-we-are-container {
                grid-template-columns: 1fr;
                gap: 40px;
                text-align: center;
            }

            .who-we-are-content h2::after {
                margin: 12px auto 0;
            }

           
        }

        @media (max-width: 600px) {
            .who-we-are-content h2 {
                font-size: 1.8rem;
            }

            .who-we-are-content p {
                font-size: 1rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .cta-content h2 {
                font-size: 2rem;
            }
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

    <!-- Who We Are Section -->
    <section style="padding:80px 0; margin-top:80px;">
        <div class="who-we-are-container">
            <div class="who-we-are-image" data-aos="fade-right" data-aos-duration="1000">
                <img src="assets/about.jpg" alt="About IndiLet - Professional Rental Services">
            </div>
            <div class="who-we-are-content" data-aos="fade-left" data-aos-duration="1000">
                <h2>Who We Are</h2>
                <p>
                    We are two passionate business professionals with over 15 years of extensive industry experience. As IIM graduates, we've worked with some of the world's leading employers across India and abroad.
                </p>
                <p>
                    Driven by a shared vision, we're on a mission to <strong>redefine the rental landscape in India</strong> by delivering a differentiated, seamless experience for <strong>corporates, property owners, and tenants</strong>. Our goal is to bring structure, trust, and professionalism to a space that's long overdue for transformation.
                </p>
               
            </div>
        </div>
    </section>

    <!-- Our Mission Section -->
    <section style="padding:80px 0; background-color: #f9f9f9;">
        <div class="who-we-are-container">
            <div class="who-we-are-content" data-aos="fade-right" data-aos-duration="1000">
                <h2>Our Mission</h2>
                <p>
                    At <strong>IndiLet</strong>, we understand that relocating to a new city for work can be both exciting and overwhelming, especially for new employees navigating unfamiliar surroundings, housing options, and daily logistics.
                </p>
                <p>
                    That's why we partner with forward-thinking organizations to offer a <strong>seamless and stress-free relocation</strong> and housing solution for their new hires. We believe in removing the complexity from corporate relocations.
                </p>
                <p>
                    Our comprehensive approach ensures that employees can focus on their career growth while we handle all aspects of their housing needs with professionalism and care.
                </p>
                
            </div>
            <div class="who-we-are-image" data-aos="fade-left" data-aos-duration="1000">
                <img src="assets/about2.jpg" alt="Our Mission - Corporate Relocation Services">
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