<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Home Easy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
</head>
<body>
    <!-- Hero Section -->
    <section class="hero">
        <video class="hero-video" autoplay muted loop>
            <source src="hero.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        <div class="hero-overlay"></div>
        
        <div class="hero-content">
            <h1 class="hero-title">Your Trustworthy Rental Partner</h1>
            <p class="hero-tagline"><strong>RENT • EXPLORE • SETTLE</strong></p>
            
            <div class="search-container">
                <div class="search-header">
                    <h3 class="search-title">Discover Your Perfect Rental Home</h3>
                </div>
                
                <div class="search-bar">
                    <div class="search-input-container">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="search-input" placeholder="Search for places, neighborhoods, or landmarks..." id="searchInput">
                    </div>
                    <button class="search-btn" onclick="performSearch()">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </div>
        </div>
    </section>
    
    <script>
        // Placeholder typing animation
        const placeholderTexts = [
            "Search for places, neighborhoods...",
            "Find apartments, houses for rent...",
            "Explore rentals near you...",
            "Discover your dream home...",
            "Search by location or landmark..."
        ];
        
        let currentTextIndex = 0;
        const searchInput = document.getElementById('searchInput');
        let typingInterval;
        
        function typeText(text, callback) {
            let index = 0;
            searchInput.placeholder = '';
            
            typingInterval = setInterval(() => {
                searchInput.placeholder += text[index];
                index++;
                
                if (index >= text.length) {
                    clearInterval(typingInterval);
                    setTimeout(callback, 1500);
                }
            }, 80);
        }
        
        function eraseText(callback) {
            const currentText = searchInput.placeholder;
            let index = currentText.length;
            
            typingInterval = setInterval(() => {
                searchInput.placeholder = currentText.substring(0, index);
                index--;
                
                if (index < 0) {
                    clearInterval(typingInterval);
                    setTimeout(callback, 300);
                }
            }, 40);
        }
        
        function cyclePlaceholder() {
            typeText(placeholderTexts[currentTextIndex], () => {
                eraseText(() => {
                    currentTextIndex = (currentTextIndex + 1) % placeholderTexts.length;
                    cyclePlaceholder();
                });
            });
        }
        
        // Start animation when page loads
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(cyclePlaceholder, 1000);
        });
        
        // Stop animation when user focuses on input
        searchInput.addEventListener('focus', function() {
            clearInterval(typingInterval);
            searchInput.placeholder = "Type your search here...";
        });
        
        // Resume animation when user leaves input empty
        searchInput.addEventListener('blur', function() {
            if (!searchInput.value) {
                setTimeout(cyclePlaceholder, 500);
            }
        });

        // Updated search functionality to navigate to listing page
        function performSearch() {
            const searchTerm = searchInput.value.trim();
            if (searchTerm) {
                const searchBtn = document.querySelector('.search-btn');
                const originalHTML = searchBtn.innerHTML;
                searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Searching...';
                
                // Encode the search term for URL
                const encodedSearchTerm = encodeURIComponent(searchTerm);
                
                // Navigate to the listing page with search parameters
                setTimeout(() => {
                    window.location.href = `http://localhost/indilet/listing.php?page=1&search=${encodedSearchTerm}&bhk=&furnished=&availability_date=&max_price=`;
                }, 1000);
            } else {
                alert('Please enter a search term');
            }
        }

        // Allow Enter key to search
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
</body>
</html>