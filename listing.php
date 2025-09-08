<?php
// Include DB connection
include 'connection.php';

// Initialize message variable
$message = '';
$messageType = 'info';

// Handle incoming messages from redirects
if (isset($_GET['message'])) {
    $message = $_GET['message'];
    $messageType = isset($_GET['type']) ? $_GET['type'] : 'info';
    
    // Convert 'error' to 'danger' for Bootstrap alert classes
    if ($messageType === 'error') {
        $messageType = 'danger';
    }
}

// Initialize search and filter variables
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$property_type = isset($_GET['property_type']) ? $_GET['property_type'] : '';
$bhk = isset($_GET['bhk']) ? $_GET['bhk'] : '';
$furnished = isset($_GET['furnished']) ? $_GET['furnished'] : '';
$area = isset($_GET['area']) ? $_GET['area'] : '';
$locality = isset($_GET['locality']) ? $_GET['locality'] : '';
$availability_date = isset($_GET['availability_date']) ? $_GET['availability_date'] : '';
$min_price = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? intval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? intval($_GET['max_price']) : 0;
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Fetch unique areas for dropdown
$area_sql = "SELECT DISTINCT area FROM rooms WHERE area IS NOT NULL AND area != '' ORDER BY area";
$area_result = $conn->query($area_sql);
$areas = [];
if ($area_result) {
    while($area_row = $area_result->fetch_assoc()) {
        $areas[] = $area_row['area'];
    }
}

// Fetch unique localities for dropdown
$locality_sql = "SELECT DISTINCT locality FROM rooms WHERE locality IS NOT NULL AND locality != '' ORDER BY locality";
$locality_result = $conn->query($locality_sql);
$localities = [];
if ($locality_result) {
    while($loc = $locality_result->fetch_assoc()) {
        $localities[] = $loc['locality'];
    }
}

// Pagination variables
$records_per_page = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $records_per_page;

// Build WHERE clause based on filters
$where_conditions = ['r.status = "available"']; // Only show available properties
$params = [];
$types = '';

// Search functionality
if (!empty($search_query)) {
    $where_conditions[] = "(r.description LIKE ? OR r.area LIKE ? OR r.locality LIKE ?)";
    $search_param = "%$search_query%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

// BHK filter
if (!empty($bhk)) {
    if ($bhk === '4') {
        $where_conditions[] = "r.room_type LIKE '4%'";
    } else {
        $where_conditions[] = "r.room_type LIKE ?";
        $params[] = "%$bhk BHK%";
        $types .= 's';
    }
}

// Furnished filter
if (!empty($furnished)) {
    $where_conditions[] = "r.furnished = ?";
    $params[] = $furnished;
    $types .= 's';
}

// Area filter
if (!empty($area)) {
    $where_conditions[] = "r.area = ?";
    $params[] = $area;
    $types .= 's';
}

// Locality filter
if (!empty($locality)) {
    $where_conditions[] = "r.locality = ?";
    $params[] = $locality;
    $types .= 's';
}

// Availability date filter
if (!empty($availability_date)) {
    $where_conditions[] = "r.availability_date <= ?";
    $params[] = $availability_date;
    $types .= 's';
}

// Price filters - Fixed logic
if ($min_price > 0) {
    $where_conditions[] = "CAST(r.rent AS UNSIGNED) >= ?";
    $params[] = $min_price;
    $types .= 'i';
}

if ($max_price > 0) {
    $where_conditions[] = "CAST(r.rent AS UNSIGNED) <= ?";
    $params[] = $max_price;
    $types .= 'i';
}

$where_clause = implode(' AND ', $where_conditions);

// Build ORDER BY clause - Fixed sorting
$order_clause = 'r.created_at DESC';
switch ($sort_by) {
    case 'price-low':
        $order_clause = 'CAST(r.rent AS UNSIGNED) ASC';
        break;
    case 'price-high':
        $order_clause = 'CAST(r.rent AS UNSIGNED) DESC';
        break;
    case 'newest':
        $order_clause = 'r.created_at DESC';
        break;
    case 'recommended':
    default:
        $order_clause = 'r.status ASC, r.created_at DESC';
        break;
}

// Count total records for pagination
$count_sql = "SELECT COUNT(*) as total FROM rooms r WHERE $where_clause";
$count_stmt = $conn->prepare($count_sql);

if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}

$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Fetch filtered rooms with primary image only (display_order = 0) with pagination
$sql = "SELECT r.*, 
               COUNT(ri.id) as image_count,
               ri_primary.image_path as primary_image
        FROM rooms r 
        LEFT JOIN room_images ri ON r.id = ri.room_id 
        LEFT JOIN room_images ri_primary ON r.id = ri_primary.room_id AND ri_primary.display_order = 0
        WHERE $where_clause
        GROUP BY r.id 
        ORDER BY $order_clause
        LIMIT $records_per_page OFFSET $offset";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Indilet - Rental Home Listings</title>
    <link rel="stylesheet" href="./css/listing.css">
        <link rel="stylesheet" href="./css/nav.css">
        <link rel="icon" type="image/x-icon" href="favicon.png">


    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- AOS CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <style>
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            margin-left: 8px;
        }
        
        .hold-badge {
            background-color: #ffc107;
            color: #212529;
        }
        
        .property-card .property-content .property-header .property-price {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        
        .alert-info {
            color: #31708f;
            background-color: #d9edf7;
            border-color: #bce8f1;
        }
        
        .alert-danger {
            color: #a94442;
            background-color: #f2dede;
            border-color: #ebccd1;
        }
        
        .alert-success {
            color: #3c763d;
            background-color: #dff0d8;
            border-color: #d6e9c6;
        }
        /* Loader screen */
#loader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: #fff;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

#loader img {
    width: 100px; /* adjust gif size */
    margin-bottom: 20px;
}

#loader p {
    font-size: 20px;
    font-weight: 600;
    color: #f68b1f;
}
    </style>
</head>
<body>
     <div id="loader">
        <img src="assets/loader.gif" alt="Loading...">
        <p>Finding Homes...</p>
    </div>
    <?php include('./components/header.php');?>

    <!-- Display Messages -->
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Search and Filters with Background Image -->
    <div class="search-section" data-aos="fade-down">
        <div class="search-container">
            <form method="GET" action="" id="searchForm">
                <!-- Search Bar -->
                <div class="search-bar" data-aos="fade-up" data-aos-delay="100">
                    <div class="search-input-wrapper">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="search" class="search-input" 
                               placeholder="Search area, property name or landmark"
                               value="<?php echo htmlspecialchars($search_query); ?>">
                    </div>
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>

                <!-- Filters -->
                <div class="filters" data-aos="fade-up" data-aos-delay="200">
                    <div class="filter-group">
                        <label class="filter-label">Area</label>
                        <select name="area" class="filter-select">
                            <option value="">All Areas</option>
                            <?php foreach($areas as $area_option): ?>
                                <option value="<?php echo htmlspecialchars($area_option); ?>" 
                                        <?php echo $area === $area_option ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($area_option); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">Locality</label>
                        <select name="locality" class="filter-select">
                            <option value="">All Localities</option>
                            <?php foreach($localities as $loc): ?>
                                <option value="<?php echo htmlspecialchars($loc); ?>" 
                                        <?php echo $locality === $loc ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($loc); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">BHK</label>
                        <select name="bhk" class="filter-select">
                            <option value="">Any BHK</option>
                            <option value="1" <?php echo $bhk === '1' ? 'selected' : ''; ?>>1 BHK</option>
                            <option value="2" <?php echo $bhk === '2' ? 'selected' : ''; ?>>2 BHK</option>
                            <option value="3" <?php echo $bhk === '3' ? 'selected' : ''; ?>>3 BHK</option>
                            <option value="4" <?php echo $bhk === '4' ? 'selected' : ''; ?>>4+ BHK</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">Furnished</label>
                        <select name="furnished" class="filter-select">
                            <option value="">Any</option>
                            <option value="fully_furnished" <?php echo $furnished === 'fully_furnished' ? 'selected' : ''; ?>>Fully Furnished</option>
                            <option value="semi_furnished" <?php echo $furnished === 'semi_furnished' ? 'selected' : ''; ?>>Semi Furnished</option>
                            <option value="unfurnished" <?php echo $furnished === 'unfurnished' ? 'selected' : ''; ?>>Unfurnished</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">Available From</label>
                        <input type="date" name="availability_date" class="filter-input" 
                               value="<?php echo htmlspecialchars($availability_date); ?>">
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">Min Price (₹/month)</label>
                        <input type="number" name="min_price" class="filter-input" 
                               placeholder="Min budget" value="<?php echo $min_price > 0 ? $min_price : ''; ?>" min="0">
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">Max Price (₹/month)</label>
                        <input type="number" name="max_price" class="filter-input" 
                               placeholder="Max budget" value="<?php echo $max_price > 0 ? $max_price : ''; ?>" min="0">
                    </div>

                    <!-- Hidden field to preserve sort when filtering -->
                    <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort_by); ?>">
                </div>
            </form>
        </div>
    </div>

    <!-- Sort and View Controls -->
    <div class="controls" data-aos="fade-in">
        <div class="controls-container">
            <div class="sort-section">
                <span class="sort-label">Sort:</span>
                <select class="sort-select" onchange="updateSort(this.value)">
                    <option value="recommended" <?php echo $sort_by === 'recommended' ? 'selected' : ''; ?>>Recommended</option>
                    <option value="price-low" <?php echo $sort_by === 'price-low' ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="price-high" <?php echo $sort_by === 'price-high' ? 'selected' : ''; ?>>Price: High to Low</option>
                    <option value="newest" <?php echo $sort_by === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Results Info -->
    <?php if ($total_records > 0): ?>
        <div class="results-info" data-aos="fade-in">
            Showing <?php echo (($page - 1) * $records_per_page) + 1; ?> to 
            <?php echo min($page * $records_per_page, $total_records); ?> of 
            <?php echo $total_records; ?> properties
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <div class="main-content" id="main-content">
        <?php if ($result->num_rows > 0): ?>
            <?php $aos_delay = 100; ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="property-card" data-aos="fade-up" data-aos-delay="<?php echo $aos_delay; ?>">
                    <div class="property-image">
                        <?php if (!empty($row['primary_image'])): ?>
                            <img src="admin/<?php echo htmlspecialchars($row['primary_image']); ?>" alt="Property Image">
                        <?php else: ?>
                            <i class="fas fa-home" style="opacity: 0.3;"></i>
                        <?php endif; ?>
                    </div>

                    <div class="property-content">
                        <div class="property-header">
                            <div class="property-price">₹<?php echo number_format($row['rent']); ?>/month</div>
                            <div class="property-details"><?php echo htmlspecialchars($row['room_type']); ?> • <?php echo ucfirst(str_replace('_', ' ', $row['furnished'])); ?></div>
                            <div class="property-location">
                                <i class="fas fa-map-marker-alt"></i>
                                <?php 
                                $location_display = [];
                                if (!empty($row['locality'])) $location_display[] = htmlspecialchars($row['locality']);
                                if (!empty($row['area'])) $location_display[] = htmlspecialchars($row['area']);
                                echo implode(', ', array_unique($location_display));
                                ?>
                            </div>
                        </div>

                        <div class="property-title">
                            <?php echo htmlspecialchars($row['description']); ?>
                        </div>

                        <div class="property-features">
                            <div class="feature">
                                <i class="fas fa-home"></i>
                                <span><?php echo htmlspecialchars($row['room_type']); ?></span>
                            </div>
                            <div class="feature">
                                <i class="fas fa-couch"></i>
                                <span><?php echo ucfirst(str_replace('_', ' ', $row['furnished'])); ?></span>
                            </div>
                            <?php if (!empty($row['area'])): ?>
                                <div class="feature">
                                    <i class="fas fa-city"></i>
                                    <span><?php echo htmlspecialchars($row['area']); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($row['locality'])): ?>
                                <div class="feature">
                                    <i class="fas fa-map-signs"></i>
                                    <span><?php echo htmlspecialchars($row['locality']); ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="feature">
                                <i class="fas fa-rupee-sign"></i>
                                <span>Deposit: ₹<?php echo number_format($row['deposit']); ?></span>
                            </div>
                            <div class="feature">
                                <i class="fas fa-calendar"></i>
                                <span>Available: <?php echo date('M d, Y', strtotime($row['availability_date'])); ?></span>
                            </div>
                            <?php if ($row['image_count'] > 0): ?>
                                <div class="feature">
                                    <i class="fas fa-images"></i>
                                    <span><?php echo $row['image_count']; ?> Photos</span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="property-footer">
                            <div class="availability">
                                Available from <?php echo date('M d, Y', strtotime($row['availability_date'])); ?>
                            </div>
                            <div class="property-actions">
                                <a href="https://wa.me/+918522863853" target="_blank" class="btn btn-outline">
                                    <i class="fab fa-whatsapp"></i> WhatsApp
                                </a>
                                <a href="tel:+918522863853" class="btn btn-outline">
                                    <i class="fas fa-phone"></i> Call
                                </a>
                                <a href="mailto:contact@indilet.com" class="btn btn-outline">
                                    <i class="fas fa-envelope"></i> Email
                                </a>
                                <a href="view_details.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php $aos_delay += 50; ?>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-results" data-aos="fade-in">
                <i class="fas fa-search"></i>
                <h3>No properties found</h3>
                <p>Try adjusting your search criteria or filters.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="pagination-wrapper" data-aos="fade-up">
            <ul class="pagination">
                <!-- Previous Page -->
                <?php if ($page > 1): ?>
                    <li>
                        <a href="<?php echo buildPaginationUrl($page - 1); ?>" title="Previous Page">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                <?php else: ?>
                    <li class="disabled">
                        <span><i class="fas fa-chevron-left"></i></span>
                    </li>
                <?php endif; ?>

                <!-- Page Numbers -->
                <?php
                $start = max(1, $page - 2);
                $end = min($total_pages, $page + 2);
                
                // Show first page if not in range
                if ($start > 1): ?>
                    <li><a href="<?php echo buildPaginationUrl(1); ?>">1</a></li>
                    <?php if ($start > 2): ?>
                        <li class="disabled"><span>...</span></li>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Page range -->
                <?php for ($i = $start; $i <= $end; $i++): ?>
                    <li <?php echo $i == $page ? 'class="active"' : ''; ?>>
                        <?php if ($i == $page): ?>
                            <span><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="<?php echo buildPaginationUrl($i); ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    </li>
                <?php endfor; ?>

                <!-- Show last page if not in range -->
                <?php if ($end < $total_pages): ?>
                    <?php if ($end < $total_pages - 1): ?>
                        <li class="disabled"><span>...</span></li>
                    <?php endif; ?>
                    <li><a href="<?php echo buildPaginationUrl($total_pages); ?>"><?php echo $total_pages; ?></a></li>
                <?php endif; ?>

                <!-- Next Page -->
                <?php if ($page < $total_pages): ?>
                    <li>
                        <a href="<?php echo buildPaginationUrl($page + 1); ?>" title="Next Page">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                <?php else: ?>
                    <li class="disabled">
                        <span><i class="fas fa-chevron-right"></i></span>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    <?php endif; ?>

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
            duration: 800,
            once: true,
            offset: 100,
            easing: 'ease-out-cubic'
        });

        function toggleHeart(button) {
            const icon = button.querySelector('i');
            const isActive = button.classList.contains('active');
            
            if (isActive) {
                button.classList.remove('active');
                icon.classList.remove('fas');
                icon.classList.add('far');
            } else {
                button.classList.add('active');
                icon.classList.remove('far');
                icon.classList.add('fas');
            }
        }

        function updateSort(value) {
            const url = new URL(window.location);
            url.searchParams.set('sort', value);
            url.searchParams.set('page', '1'); // Reset to first page
            window.location = url;
        }

        // Handle form submission for search and filters
        document.getElementById('searchForm').addEventListener('submit', function(e) {
            // Reset to page 1 when submitting form
            let pageInput = this.querySelector('input[name="page"]');
            if (!pageInput) {
                pageInput = document.createElement('input');
                pageInput.type = 'hidden';
                pageInput.name = 'page';
                this.appendChild(pageInput);
            }
            pageInput.value = '1';
        });

        // Keep the enter key functionality for search input
        document.querySelector('.search-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('searchForm').submit();
            }
        });

        // Smooth scroll to top when pagination is clicked
        document.querySelectorAll('.pagination a').forEach(link => {
            link.addEventListener('click', function(e) {
                // Smooth scroll to search section
                document.querySelector('.search-section').scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>

<?php
// Function to build pagination URL with current filters
function buildPaginationUrl($page) {
    $params = $_GET;
    $params['page'] = $page;
    return '?' . http_build_query($params);
}

// Clean up
if (isset($stmt)) $stmt->close();
if (isset($count_stmt)) $count_stmt->close();
$conn->close();
?>
