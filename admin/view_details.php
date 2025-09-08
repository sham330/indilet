<?php
// Include DB connection
include 'connection.php';

// Initialize variables
$property = null;
$images = [];
$message = '';
$messageType = 'info';

// Get property ID from URL - treating as string since you mentioned it's a string
$property_id = isset($_GET['id']) ? trim($_GET['id']) : '';

if (empty($property_id)) {
    header("Location: index.php?message=" . urlencode("Invalid property ID") . "&type=error");
    exit();
}

try {
    // Fetch property details - removed status filter to show both available and hold properties
    $property_sql = "SELECT * FROM rooms WHERE id = ?";
    $property_stmt = $conn->prepare($property_sql);
    $property_stmt->bind_param('s', $property_id); // Changed from 'i' to 's' for string
    $property_stmt->execute();
    $property_result = $property_stmt->get_result();
    
    if ($property_result->num_rows === 0) {
        header("Location: index.php?message=" . urlencode("Property not found") . "&type=error");
        exit();
    }
    
    $property = $property_result->fetch_assoc();
    
    // Fetch all images for this property ordered by display_order (0 first)
    $images_sql = "SELECT image_path FROM room_images WHERE room_id = ? ORDER BY display_order ASC, id ASC";
    $images_stmt = $conn->prepare($images_sql);
    $images_stmt->bind_param('s', $property_id); // Using string binding
    $images_stmt->execute();
    $images_result = $images_stmt->get_result();
    
    while ($row = $images_result->fetch_assoc()) {
        $images[] = $row['image_path'];
    }
    
    // If no images found, use default placeholder
    if (empty($images)) {
        $images[] = 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=720';
    }
    
} catch (Exception $e) {
    error_log("Property details error: " . $e->getMessage()); // Log error for debugging
    header("Location: index.php?message=" . urlencode("Error loading property details") . "&type=error");
    exit();
}

// Format functions
function formatPrice($price) {
    return '₹' . number_format($price);
}

function formatDate($date) {
    if ($date && $date !== '0000-00-00') {
        return date('M d, Y', strtotime($date));
    }
    return 'Available Now';
}

function getAvailabilityStatus($date, $status) {
    if ($status === 'hold') {
        return 'Currently on Hold';
    }
    
    if (!$date || $date === '0000-00-00') {
        return 'Available Now';
    }
    
    $availability = strtotime($date);
    $today = strtotime(date('Y-m-d'));
    
    if ($availability <= $today) {
        return 'Available Now';
    } else {
        return 'Available from ' . date('M d, Y', $availability);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($property['description'] ?? 'Property Details'); ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/details.css">
</head>
<body>
<a href="index.php?tab=listings" style="display:inline-block; padding:10px 18px; background-color:#ff6600; color:#fff; text-decoration:none; border-radius:6px; font-weight:bold; transition:0.3s;">
  Go back
</a>
  <div class="container">
    <div class="property-card">
      <!-- Gallery Section -->
      <div class="gallery">
        <?php foreach ($images as $index => $image): ?>
        <img src="<?php echo htmlspecialchars($image); ?>" 
             class="gallery-media <?php echo $index === 0 ? 'active' : ''; ?>" 
             id="media-<?php echo $index; ?>" 
             alt="Property Image <?php echo $index + 1; ?>"
             loading="<?php echo $index === 0 ? 'eager' : 'lazy'; ?>"
             >
        <?php endforeach; ?>
        
        <?php if (count($images) > 1): ?>
        <!-- Navigation Arrows -->
        <button class="gallery-nav prev" onclick="prevImage()" aria-label="Previous image">
          <i class="fas fa-chevron-left"></i>
        </button>
        <button class="gallery-nav next" onclick="nextImage()" aria-label="Next image">
          <i class="fas fa-chevron-right"></i>
        </button>
        
        <!-- Image Counter -->
        <div class="gallery-counter">
          <span id="current-image">1</span> / <?php echo count($images); ?>
        </div>
        
        <!-- Dots Navigation -->
        <div class="gallery-controls">
          <?php foreach ($images as $index => $image): ?>
          <button class="gallery-dot <?php echo $index === 0 ? 'active' : ''; ?>" 
                  onclick="showMedia(<?php echo $index; ?>)" 
                  aria-label="View image <?php echo $index + 1; ?>"></button>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
      
      <!-- Property Information -->
      <div class="property-info">
        <h1 class="property-title"><?php echo htmlspecialchars($property['description'] ?? 'Property Details'); ?></h1>
        
        <!-- Details Grid -->
        <div class="details-grid">
          <div class="detail-card">
            <div class="detail-header">
              <div class="detail-icon">
                <i class="fas fa-rupee-sign"></i>
              </div>
              <div class="detail-label">Monthly Rent</div>
            </div>
            <div class="detail-value"><?php echo formatPrice($property['rent']); ?></div>
          </div>
          
          <div class="detail-card">
            <div class="detail-header">
              <div class="detail-icon">
                <i class="fas fa-shield-alt"></i>
              </div>
              <div class="detail-label">Security Deposit</div>
            </div>
            <div class="detail-value"><?php echo formatPrice($property['deposit']); ?></div>
          </div>
          
          <div class="detail-card">
            <div class="detail-header">
              <div class="detail-icon">
                <i class="fas fa-calendar-check"></i>
              </div>
              <div class="detail-label">Availability</div>
            </div>
            <div class="detail-value"><?php echo getAvailabilityStatus($property['availability_date'], $property['status']); ?></div>
          </div>
          
          <!-- Status Field -->
          <div class="detail-card">
            <div class="detail-header">
              <div class="detail-icon">
                <i class="fas fa-info-circle"></i>
              </div>
              <div class="detail-label">Status</div>
            </div>
            <div class="detail-value"><?php echo ucfirst(htmlspecialchars($property['status'] ?? 'Available')); ?></div>
          </div>
          
          <div class="detail-card">
            <div class="detail-header">
              <div class="detail-icon">
                <i class="fas fa-home"></i>
              </div>
              <div class="detail-label">Configuration</div>
            </div>
            <div class="detail-value"><?php echo htmlspecialchars($property['room_type'] ?? 'N/A'); ?></div>
          </div>
          
          <div class="detail-card">
            <div class="detail-header">
              <div class="detail-icon">
                <i class="fas fa-couch"></i>
              </div>
              <div class="detail-label">Furnishing</div>
            </div>
            <div class="detail-value"><?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($property['furnished'] ?? 'N/A'))); ?></div>
          </div>

          <div class="detail-card">
            <div class="detail-header">
              <div class="detail-icon">
                <i class="fas fa-map-marker-alt"></i>
              </div>
              <div class="detail-label">Area</div>
            </div>
            <div class="detail-value"><?php echo htmlspecialchars($property['area'] ?? 'Area not specified'); ?></div>
          </div>
          
          <!-- Locality Field -->
          <div class="detail-card">
            <div class="detail-header">
              <div class="detail-icon">
                <i class="fas fa-map-signs"></i>
              </div>
              <div class="detail-label">Locality</div>
            </div>
            <div class="detail-value"><?php echo htmlspecialchars($property['locality'] ?? 'Locality not specified'); ?></div>
          </div>
          
          <div class="detail-card">
            <div class="detail-header">
              <div class="detail-icon">
                <i class="fas fa-calendar-alt"></i>
              </div>
              <div class="detail-label">Listed On</div>
            </div>
            <div class="detail-value"><?php echo date('M d, Y', strtotime($property['created_at'])); ?></div>
          </div>
        </div>
      </div>
      
     
    </div>
  </div>

  <script>
    let currentImageIndex = 0;
    const totalImages = <?php echo count($images); ?>;

    function showMedia(index) {
      // Validate index
      if (index < 0 || index >= totalImages) {
        return;
      }

      // Hide all media elements
      document.querySelectorAll('.gallery-media').forEach((media, i) => {
        media.classList.remove('active');
      });
      
      // Show selected media
      const targetMedia = document.getElementById(`media-${index}`);
      if (targetMedia) {
        targetMedia.classList.add('active');
      }
      
      // Update dots
      document.querySelectorAll('.gallery-dot').forEach((dot, i) => {
        if (i === index) {
          dot.classList.add('active');
        } else {
          dot.classList.remove('active');
        }
      });

      // Update counter
      currentImageIndex = index;
      const counterElement = document.getElementById('current-image');
      if (counterElement) {
        counterElement.textContent = index + 1;
      }
    }

    function nextImage() {
      if (totalImages <= 1) return;
      const nextIndex = (currentImageIndex + 1) % totalImages;
      showMedia(nextIndex);
    }

    function prevImage() {
      if (totalImages <= 1) return;
      const prevIndex = (currentImageIndex - 1 + totalImages) % totalImages;
      showMedia(prevIndex);
    }

    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
      if (e.key === 'ArrowRight') {
        e.preventDefault();
        nextImage();
      } else if (e.key === 'ArrowLeft') {
        e.preventDefault();
        prevImage();
      } else if (e.key === 'Escape') {
        window.location.href = 'index.php';
      }
    });

    // Touch/swipe support for mobile
    let touchStartX = 0;
    let touchEndX = 0;
    let touchStartY = 0;
    let touchEndY = 0;

    const gallery = document.querySelector('.gallery');

    if (gallery && totalImages > 1) {
      gallery.addEventListener('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
        touchStartY = e.changedTouches[0].screenY;
      }, { passive: true });

      gallery.addEventListener('touchend', function(e) {
        touchEndX = e.changedTouches[0].screenX;
        touchEndY = e.changedTouches[0].screenY;
        handleSwipe();
      }, { passive: true });
    }

    function handleSwipe() {
      const swipeThreshold = 50;
      const diffX = touchStartX - touchEndX;
      const diffY = Math.abs(touchStartY - touchEndY);

      // Only handle horizontal swipes (ignore vertical scrolling)
      if (Math.abs(diffX) > swipeThreshold && diffY < 100) {
        if (diffX > 0) {
          // Swiped left - show next image
          nextImage();
        } else {
          // Swiped right - show previous image
          prevImage();
        }
      }
    }

    // Preload adjacent images for better performance
    function preloadAdjacentImages() {
      if (totalImages <= 1) return;
      
      const nextIndex = (currentImageIndex + 1) % totalImages;
      const prevIndex = (currentImageIndex - 1 + totalImages) % totalImages;
      
      [nextIndex, prevIndex].forEach(index => {
        const img = document.getElementById(`media-${index}`);
        if (img && !img.complete) {
          // Image will load due to lazy loading when needed
        }
      });
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
      if (totalImages > 1) {
        preloadAdjacentImages();
      }
    });
  </script>
</body>
</html>
