<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>What We Do - IndiLet</title>
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
    font-family: 'Roboto', sans-serif;
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

        /* Service Section Styles */
        .service-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
            max-width: 1180px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        .service-image {
            position: relative;
            overflow: hidden;
            border-radius: 20px;
            box-shadow: 0 12px 32px rgba(0,0,0,0.1);
        }

        .service-image img {
            width: 100%;
            height: auto;
            display: block;
            border-radius: 20px;
            transition: transform 0.3s ease;
        }

        .service-image:hover img {
            transform: scale(1.05);
        }

        .service-content {
            padding: 20px 0;
        }

        .service-content h2 {
            font-size: 2.2rem;
            color: #222;
            font-weight: 700;
            margin-bottom: 24px;
            position: relative;
        }

        .service-content h2::after {
            content: '';
            display: block;
            width: 50px;
            height: 4px;
            background: #ff7300;
            border-radius: 2px;
            margin-top: 12px;
        }

        .service-content p {
            font-size: 1.1rem;
            line-height: 1.7;
            color: #444;
            margin-bottom: 20px;
        }

        .service-content p.highlight {
            font-size: 1.2rem;
            font-weight: 600;
            color: #222;
            margin-bottom: 15px;
        }

        

        
        /* Responsive Design */
        @media (max-width: 968px) {
            .service-container {
                grid-template-columns: 1fr;
                gap: 40px;
                text-align: center;
            }

            .service-content h2::after {
                margin: 12px auto 0;
            }

         
        }

        @media (max-width: 600px) {
            .service-content h2 {
                font-size: 1.8rem;
            }

            .service-content p {
                font-size: 1rem;
            }

            .section-title {
                font-size: 2rem;
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

    <!-- The Problem Section -->
    <section style="padding:80px 0; margin-top:80px">
        <div class="service-container">
            <div class="service-image" data-aos="fade-right" data-aos-duration="1000">
                <img src="assets/what-we-do.jpg" alt="Housing Problems - Corporate Relocation Challenges">
            </div>
            <div class="service-content" data-aos="fade-left" data-aos-duration="1000">
                <h2>What We Do</h2>
                <p class="highlight">Solving a Real Problem for Corporates and their Employees</p>
                <p>
                    Every day, employees join companies from different cities, often provided with temporary accommodation for a month or two. Once that period ends, they're left to navigate the complex and often unreliable housing market on their own.
                </p>
                <p>
                    From dealing with unverified listings and unregulated brokers to falling prey to scams, new hires can easily find themselves distracted, anxious, and financially impacted, just when they should be focusing on integrating into the company and contributing to business goals.
                </p>
                
               
        </div>
    </section>

    <!-- Our Solution Section -->
    <section style="padding:80px 0; background-color: #f9f9f9;">
        <div class="service-container">
            <div class="service-content" data-aos="fade-right" data-aos-duration="1000">
                <h2>Our Solution</h2>
                <p>
                    We offer an <strong>end-to-end, one-stop solution</strong> to help new employees find a home that meets their specific needs—quickly and hassle-free. From property search to final move-in, we take care of everything, enabling the employee to focus on their role and adjust smoothly to their new environment.
                </p>
                <p>
                    By streamlining the relocation process, organizations can <strong>reduce hiring-related accommodation costs by up to 50%</strong>, without compromising on the quality of the employee's stay.
                </p>
                <p>
                    This approach frees up valuable time and mental bandwidth for the new hire, allowing for faster learning, greater engagement, and ultimately, higher productivity—delivering direct value to the organization.
                </p>

               

               
            </div>
            <div class="service-image" data-aos="fade-left" data-aos-duration="1000">
                <img src="assets/what-we-do2.jpg" alt="Our Solution - End-to-End Relocation Services">
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