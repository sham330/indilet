<?php
// Include authentication check
require_once 'auth_check.php';

// Include DB connection
include 'connection.php';

// Get current user info
$current_user = getCurrentUser();

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

// Handle Delete Room
if (isset($_GET['delete_room']) && isAdmin()) {
    $roomId = trim($_GET['delete_room']);
    
    try {
        $conn->begin_transaction();
        
        // Get all images for deletion
        $imagesSql = "SELECT image_path FROM room_images WHERE room_id = ?";
        $imagesStmt = $conn->prepare($imagesSql);
        $imagesStmt->bind_param("s", $roomId);
        $imagesStmt->execute();
        $imagesResult = $imagesStmt->get_result();
        
        // Delete image files
        while ($imageRow = $imagesResult->fetch_assoc()) {
            $imagePath = $imageRow['image_path'];
            if (!str_starts_with($imagePath, 'uploads/rooms/')) {
                $imagePath = 'uploads/rooms/' . basename($imagePath);
            }
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        $imagesStmt->close();
        
        // Delete room
        $sql = "DELETE FROM rooms WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $roomId);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $conn->commit();
            header("Location: index.php?tab=listings&message=" . urlencode("Room deleted successfully!") . "&type=success");
            exit();
        } else {
            throw new Exception("Room not found!");
        }
        $stmt->close();
        
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: index.php?tab=listings&message=" . urlencode($e->getMessage()) . "&type=error");
        exit();
    }
}

// Handle Delete Image
if (isset($_GET['delete_image']) && isAdmin()) {
    $imageId = trim($_GET['delete_image']);
    
    try {
        // Get image path
        $imageSql = "SELECT image_path FROM room_images WHERE id = ?";
        $imageStmt = $conn->prepare($imageSql);
        $imageStmt->bind_param("i", $imageId);
        $imageStmt->execute();
        $imageResult = $imageStmt->get_result();
        $imageData = $imageResult->fetch_assoc();
        $imageStmt->close();
        
        if ($imageData) {
            $imagePath = $imageData['image_path'];
            if (!str_starts_with($imagePath, 'uploads/rooms/')) {
                $imagePath = 'uploads/rooms/' . basename($imagePath);
            }
            
            // Delete file
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
            
            // Delete from database
            $deleteSql = "DELETE FROM room_images WHERE id = ?";
            $deleteStmt = $conn->prepare($deleteSql);
            $deleteStmt->bind_param("i", $imageId);
            $deleteStmt->execute();
            $deleteStmt->close();
            
            header("Location: index.php?tab=listings&message=" . urlencode("Image deleted successfully!") . "&type=success");
            exit();
        }
    } catch (Exception $e) {
        header("Location: index.php?tab=listings&message=" . urlencode("Error deleting image: " . $e->getMessage()) . "&type=error");
        exit();
    }
}

// Get search and filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : '';

// Build the SQL query with search and filters
$sql = "SELECT r.*, 
               COUNT(ri.id) as image_count,
               ri_primary.image_path as primary_image
        FROM rooms r 
        LEFT JOIN room_images ri ON r.id = ri.room_id 
        LEFT JOIN room_images ri_primary ON r.id = ri_primary.room_id AND ri_primary.display_order = 0";

$whereConditions = [];
$params = [];
$paramTypes = '';

// Add search condition - Updated to include locality
if (!empty($search)) {
    $whereConditions[] = "(r.description LIKE ? OR r.area LIKE ? OR r.locality LIKE ? OR r.room_type LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $paramTypes .= 'ssss';
}

// Add status filter
if (!empty($statusFilter)) {
    $whereConditions[] = "r.status = ?";
    $params[] = $statusFilter;
    $paramTypes .= 's';
}

// Add WHERE clause if there are conditions
if (!empty($whereConditions)) {
    $sql .= " WHERE " . implode(" AND ", $whereConditions);
}

$sql .= " GROUP BY r.id ORDER BY r.created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($paramTypes, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Listings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --accent-orange: #fd7e14;
            --text-dark: #212529;
            --text-muted: #6c757d;
            --light-gray: #f8f9fa;
            --border-light: #e9ecef;
            --pure-white: #ffffff;
            --success-green: #198754;
            --danger-red: #dc3545;
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.06);
            --shadow-md: 0 4px 16px rgba(0,0,0,0.1);
            --border-radius: 12px;
        }

        body {
            background-color: #fafbfc;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica Neue', Arial, sans-serif;
        }

        .room-management-container {
            max-width: 100%;
            padding: 2rem 1rem;
        }

        /* Header Section */
        .page-header {
            background: linear-gradient(135deg, var(--accent-orange), #ff8c42);
            color: white;
            padding: 3rem 0;
            margin: -2rem -1rem 3rem;
            border-radius: 0 0 var(--border-radius) var(--border-radius);
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
        }

        .page-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            margin: 0.5rem 0 0;
        }

        /* Search and Filter Section */
        .search-filter-section {
            background: var(--pure-white);
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-sm);
        }

        .search-box {
            position: relative;
        }

        .search-input {
            border: 2px solid var(--border-light);
            border-radius: 8px;
            padding: 0.75rem 1rem 0.75rem 3rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            border-color: var(--accent-orange);
            box-shadow: 0 0 0 0.2rem rgba(253, 126, 20, 0.25);
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        .filter-select {
            border: 2px solid var(--border-light);
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .filter-select:focus {
            border-color: var(--accent-orange);
            box-shadow: 0 0 0 0.2rem rgba(253, 126, 20, 0.25);
        }

        .active-filters {
            margin-top: 1rem;
        }

        .filter-tag {
            display: inline-block;
            background: var(--accent-orange);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            margin: 0.25rem;
        }

        /* Statistics Overview */
        .stats-overview {
            margin-bottom: 3rem;
        }

        .stat-card {
            background: var(--pure-white);
            border: none;
            border-radius: var(--border-radius);
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-sm);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--accent-orange);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--accent-orange);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1rem;
            color: var(--text-muted);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Room Cards */
        .room-card {
            background: var(--pure-white);
            border: none;
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .room-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .room-card-content {
            display: flex;
            align-items: stretch;
            min-height: 200px;
        }

        .room-details {
            flex: 1;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
        }

        .room-image-container {
            width: 300px;
            position: relative;
            overflow: hidden;
        }

        .room-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .room-card:hover .room-image {
            transform: scale(1.05);
        }

        .image-placeholder {
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, #f1f3f4, #e8eaed);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            font-size: 3rem;
        }

        .image-count-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .room-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.75rem;
            line-height: 1.3;
        }

        .room-info {
            flex: 1;
        }

        .room-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            margin-bottom: 1rem;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .meta-item i {
            color: var(--accent-orange);
            width: 16px;
        }

        .price-section {
            margin: 1rem 0;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid var(--success-green);
        }

        .rent-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--success-green);
            margin-bottom: 0.25rem;
        }

        .deposit-price {
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .room-status {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .room-status.available {
            background: #d1e7dd;
            color: #0a3622;
        }

        .room-status.occupied {
            background: #f8d7da;
            color: #58151c;
        }

        .room-status.maintenance {
            background: #fff3cd;
            color: #664d03;
        }

        .room-status.hold {
            background: #fff3cd;
            color: #664d03;
        }

        .room-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border-light);
        }

        .btn {
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.9rem;
            border: 1px solid;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: var(--accent-orange);
            border-color: var(--accent-orange);
            color: white;
        }

        .btn-primary:hover {
            background: #e86b00;
            border-color: #e86b00;
            color: white;
            transform: translateY(-1px);
        }

        .btn-outline-primary {
            background: transparent;
            border-color: var(--accent-orange);
            color: var(--accent-orange);
        }

        .btn-outline-primary:hover {
            background: var(--accent-orange);
            border-color: var(--accent-orange);
            color: white;
        }

        .btn-outline-success {
            background: transparent;
            border-color: var(--success-green);
            color: var(--success-green);
        }

        .btn-outline-danger {
            background: transparent;
            border-color: var(--danger-red);
            color: var(--danger-red);
        }

        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: var(--pure-white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--text-muted);
            margin-bottom: 1.5rem;
            opacity: 0.5;
        }

        .availability-date {
            font-size: 0.9rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        /* Results Info */
        .results-info {
            background: var(--light-gray);
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            border-left: 4px solid var(--accent-orange);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .room-card-content {
                flex-direction: column;
            }

            .room-image-container {
                width: 100%;
                height: 200px;
            }

            .room-meta {
                flex-direction: column;
                gap: 0.75rem;
            }

            .room-actions {
                flex-wrap: wrap;
            }

            .page-title {
                font-size: 2rem;
            }
        }

        @media (max-width: 576px) {
            .room-management-container {
                padding: 1rem 0.5rem;
            }

            .room-details {
                padding: 1rem;
            }

            .stat-card {
                padding: 1.5rem;
            }

            .search-filter-section {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>

<!-- Message Alert -->
<?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show mb-4" role="alert">
        <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : ($messageType === 'danger' ? 'exclamation-triangle' : 'info-circle'); ?> me-2"></i>
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">&times;</button>
    </div>
<?php endif; ?>

<div class="container-fluid room-management-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="page-title">
                        <i class="fas fa-building me-3"></i>
                        Room Listings
                    </h1>
                    <p class="page-subtitle">Manage your property listings with ease</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <?php if (isAdmin()): ?>
                        <a href="new_listing.php" class="btn btn-light btn-lg">
                            <i class="fas fa-plus me-2"></i>
                            New Listing
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Search and Filter Section -->
        <div class="search-filter-section">
            <form method="GET" action="index.php">
                <input type="hidden" name="tab" value="listings">
                <div class="row g-3">
                    <!-- Search Box -->
                    <div class="col-md-8">
                        <div class="search-box">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" name="search" class="form-control search-input" 
                                   placeholder="Search by description, area, locality, or room type..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    
                    <!-- Status Filter -->
                    <div class="col-md-3">
                        <select name="status" class="form-select filter-select">
                            <option value="">All Status</option>
                            <option value="available" <?php echo $statusFilter === 'available' ? 'selected' : ''; ?>>Available</option>
                            <option value="occupied" <?php echo $statusFilter === 'occupied' ? 'selected' : ''; ?>>Occupied</option>
                            <option value="maintenance" <?php echo $statusFilter === 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                            <option value="hold" <?php echo $statusFilter === 'hold' ? 'selected' : ''; ?>>Hold</option>
                        </select>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>

                <!-- Clear Filter Button -->
                <?php if (!empty($search) || !empty($statusFilter)): ?>
                    <div class="mt-3">
                        <a href="index.php?tab=listings" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>
                            Clear All Filters
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Active Filters Display -->
                <?php 
                $activeFilters = [];
                if (!empty($search)) $activeFilters[] = "Search: " . $search;
                if (!empty($statusFilter)) $activeFilters[] = "Status: " . ucfirst($statusFilter);
                ?>

                <?php if (!empty($activeFilters)): ?>
                    <div class="active-filters">
                        <strong>Active Filters:</strong>
                        <?php foreach ($activeFilters as $filter): ?>
                            <span class="filter-tag">
                                <?php echo htmlspecialchars($filter); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </form>
        </div>

        <!-- Results Info -->
        <?php if (!empty($activeFilters) || $result->num_rows > 0): ?>
            <div class="results-info">
                <i class="fas fa-info-circle me-2"></i>
                <?php if (!empty($activeFilters)): ?>
                    Showing <strong><?php echo $result->num_rows; ?></strong> filtered results
                <?php else: ?>
                    Showing <strong><?php echo $result->num_rows; ?></strong> total listings
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Statistics Overview -->
        <?php
        // Calculate statistics for current filtered results
        $availableCount = 0;
        $occupiedCount = 0;
        $maintenanceCount = 0;
        $holdCount = 0;
        $totalCount = $result->num_rows;
        
        $tempResult = $result;
        while($row = $tempResult->fetch_assoc()) {
            switch($row['status']) {
                case 'available': $availableCount++; break;
                case 'occupied': $occupiedCount++; break;
                case 'maintenance': $maintenanceCount++; break;
                case 'hold': $holdCount++; break;
            }
        }
        $result->data_seek(0);
        ?>

        <div class="stats-overview">
            <div class="row g-4">
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $totalCount; ?></div>
                        <div class="stat-label"><?php echo !empty($activeFilters) ? 'Filtered Results' : 'Total Rooms'; ?></div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $availableCount; ?></div>
                        <div class="stat-label">Available</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $occupiedCount; ?></div>
                        <div class="stat-label">Occupied</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $maintenanceCount + $holdCount; ?></div>
                        <div class="stat-label">Maintenance/Hold</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rooms Listing -->
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="room-card">
                    <div class="room-card-content">
                        <div class="room-details">
                            <div class="room-info">
                                <h3 class="room-title"><?php echo htmlspecialchars($row['description']); ?></h3>
                                
                                <div class="room-meta">
                                    <div class="meta-item">
                                        <i class="fas fa-home"></i>
                                        <span><?php echo htmlspecialchars($row['room_type']); ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?php echo htmlspecialchars($row['area']); ?></span>
                                    </div>
                                    <!-- Added Locality -->
                                    <?php if (!empty($row['locality'])): ?>
                                    <div class="meta-item">
                                        <i class="fas fa-map-signs"></i>
                                        <span><?php echo htmlspecialchars($row['locality']); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <div class="meta-item">
                                        <i class="fas fa-couch"></i>
                                        <span><?php echo ucfirst(str_replace('_', ' ', $row['furnished'])); ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-calendar"></i>
                                        <span class="availability-date">Available from <?php echo date('M d, Y', strtotime($row['availability_date'])); ?></span>
                                    </div>
                                </div>

                                <div class="price-section">
                                    <div class="rent-price">₹<?php echo number_format($row['rent']); ?>/month</div>
                                    <div class="deposit-price">Security Deposit: ₹<?php echo number_format($row['deposit']); ?></div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="room-status <?php echo $row['status']; ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="room-actions">
                                <a href="view_details.php?id=<?php echo urlencode($row['id']); ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                                <?php if (isAdmin()): ?>
                                    <a href="edit_listing.php?id=<?php echo urlencode($row['id']); ?>" class="btn btn-outline-success btn-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="listings.php?delete_room=<?php echo urlencode($row['id']); ?>" 
                                       class="btn btn-outline-danger btn-sm" 
                                       onclick="return confirm('Are you sure you want to delete this room and all its images?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="room-image-container">
                            <?php if (!empty($row['primary_image']) && file_exists($row['primary_image'])): ?>
                                <img src="<?php echo htmlspecialchars($row['primary_image']); ?>" 
                                     alt="Room Image" class="room-image">
                                <?php if ($row['image_count'] > 1): ?>
                                    <div class="image-count-badge">
                                        <i class="fas fa-images"></i>
                                        +<?php echo $row['image_count'] - 1; ?>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="image-placeholder">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-search"></i>
                <h3 class="text-muted mb-3">
                    <?php echo !empty($activeFilters) ? 'No Matching Results' : 'No Rooms Available'; ?>
                </h3>
                <p class="text-muted mb-4">
                    <?php if (!empty($activeFilters)): ?>
                        No rooms match your current search criteria. Try adjusting your filters or search terms.
                    <?php else: ?>
                        You haven't added any room listings yet. Start by creating your first listing.
                    <?php endif; ?>
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Auto-hide alerts
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        if (alert.querySelector('.btn-close')) {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }
    });
}, 5000);

// Add loading animation for images
document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('.room-image');
    images.forEach(img => {
        img.addEventListener('load', function() {
            this.style.opacity = '1';
        });
    });
});
</script>

<?php
$stmt->close();
$conn->close();
?>

</body>
</html>
