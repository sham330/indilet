<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Indilet - Professional Rental Services</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        
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
    
    /* New Who We Are Section Styles */
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
}

  </style>
</head>
<body>
    <section style="padding:80px 0;">
        <div class="who-we-are-container">
            <div class="who-we-are-image">
                <img src="assets/about.jpg" alt="About Indilet - Professional Rental Services">
            </div>
            <div class="who-we-are-content">
                <h2>Who We Are</h2>
                <p>
                    We are two passionate business professionals with over 15 years of extensive industry experience. As IIM graduates, we've worked with some of the world's leading employers across India and abroad.
                </p>
                <p>
                    Driven by a shared vision, we're on a mission to <strong>redefine the rental landscape in India</strong> by delivering a differentiated, seamless experience for <strong>corporates, property owners, and tenants</strong>. Our goal is to bring structure, trust, and professionalism to a space that's long overdue for transformation.
                </p>
                <a href="about-us" class="view-more-btn">
                    View More <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    <section style="padding:80px 0; background-color: #f9f9f9;">
        <div class="who-we-are-container">
            <div class="who-we-are-content">
                <h2>What We Do</h2>
                <p>
                    Every day, employees relocate for new opportunities but face complex housing challenges after temporary accommodation ends. From unverified listings to unreliable brokers and potential scams, new hires often get distracted when they should focus on their careers.
                </p>
                <p>
                    We provide an <strong>end-to-end relocation solution</strong> that handles everything from property search to final move-in. Our streamlined process helps organizations <strong>reduce accommodation costs by up to 50%</strong> while ensuring quality living for employees.
                </p>
                <p>
                    By removing housing stress, we enable faster integration, higher productivity, and better employee satisfaction—delivering direct value to both companies and their teams.
                </p>
                <a href="what-we-do" class="view-more-btn">
                    Learn More <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="who-we-are-image">
                <img src="assets/what-we-do.jpg" alt="What Indilet Does - Corporate Relocation Services">
            </div>
        </div>
    </section>

    <section style="padding:80px 0;">
        <div class="who-we-are-container">
            <div class="who-we-are-image">
                <img src="assets/what-we-offer.jpg" alt="What Indilet Offers - Comprehensive Rental Services">
            </div>
            <div class="who-we-are-content">
                <h2>What We Offer</h2>
                <p>
                    At IndiLet, our mission is to make renting simple, transparent, and stress-free—so employees can focus on settling into their new home and role with ease.
                </p>
                <p>
                    We offer a comprehensive range of services including <strong>corporate partnerships</strong>, <strong>property onboarding</strong>, <strong>end-to-end relocation management</strong>, and <strong>temporary accommodation solutions</strong>.
                </p>
                <p>
                    Our employee-centric approach includes curated add-on services like cleaning, internet setup, utility management, and on-demand assistance—all tailored to create a fully customized experience.
                </p>
                <a href="services" class="view-more-btn">
                    Learn More <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>
</body>
</html>
