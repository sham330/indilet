<?php
// Include authentication check
require_once 'auth_check.php';

// Include DB connection
include 'connection.php';

// Initialize message variable
$message = '';
$messageType = 'info';

// Get room ID from URL
$roomId = isset($_GET['id']) ? trim($_GET['id']) : '';

if (empty($roomId)) {
    header('Location: index.php?tab=listings');
    exit();
}

// Create uploads directory if it doesn't exist
$uploadDir = 'uploads/rooms/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Fetch existing room data
$roomData = null;
$existingImages = [];

try {
    // Get room details
    $sql = "SELECT * FROM rooms WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $roomId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $message = "Room not found!";
        $messageType = 'danger';
        header('Location: index.php?tab=listings');
        exit();
    }
    
    $roomData = $result->fetch_assoc();
    $stmt->close();
    
    // Get existing images
    $imageSql = "SELECT * FROM room_images WHERE room_id = ? ORDER BY display_order ASC";
    $imageStmt = $conn->prepare($imageSql);
    $imageStmt->bind_param("s", $roomId);
    $imageStmt->execute();
    $imageResult = $imageStmt->get_result();
    
    while ($imageRow = $imageResult->fetch_assoc()) {
        $existingImages[] = $imageRow;
    }
    $imageStmt->close();
    
} catch (Exception $e) {
    $message = "Error fetching room data: " . $e->getMessage();
    $messageType = 'danger';
}

// Handle Update Room
if (isset($_POST['update_room'])) {
    $description = trim($_POST['description']);
    $rent = trim($_POST['rent']);
    $deposit = trim($_POST['deposit']);
    $availability_date = trim($_POST['availability_date']);
    $room_type = trim($_POST['room_type']);
    $status = trim($_POST['status']);
    $furnished = trim($_POST['furnished']);
    $area = trim($_POST['area']);
    $locality = trim($_POST['locality']);
    
    // Validate inputs
    if (empty($description) || empty($rent) || empty($deposit) || empty($availability_date) || 
        empty($room_type) || empty($status) || empty($furnished) || empty($area) || empty($locality)) {
        $message = "All fields are required!";
        $messageType = 'danger';
    } else {
        try {
            // Start transaction
            $conn->begin_transaction();
            
            // Update room details
            $sql = "UPDATE rooms SET description = ?, rent = ?, deposit = ?, availability_date = ?, room_type = ?, status = ?, furnished = ?, area = ?, locality = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            
            if ($stmt === false) {
                throw new Exception("Error preparing statement: " . $conn->error);
            }
            
            $stmt->bind_param("ssssssssss", $description, $rent, $deposit, $availability_date, $room_type, $status, $furnished, $area, $locality, $roomId);
            
            if ($stmt->execute()) {
                // Handle image deletions
                if (isset($_POST['deleted_images']) && !empty($_POST['deleted_images'])) {
                    $deletedImages = json_decode($_POST['deleted_images'], true);
                    
                    if (is_array($deletedImages)) {
                        foreach ($deletedImages as $imageId) {
                            // Get image path before deleting from database
                            $getImageSql = "SELECT image_path FROM room_images WHERE id = ? AND room_id = ?";
                            $getImageStmt = $conn->prepare($getImageSql);
                            $getImageStmt->bind_param("is", $imageId, $roomId);
                            $getImageStmt->execute();
                            $imageResult = $getImageStmt->get_result();
                            
                            if ($imageRow = $imageResult->fetch_assoc()) {
                                // Delete physical file
                                if (file_exists($imageRow['image_path'])) {
                                    unlink($imageRow['image_path']);
                                }
                            }
                            $getImageStmt->close();
                            
                            // Delete from database
                            $deleteImageSql = "DELETE FROM room_images WHERE id = ? AND room_id = ?";
                            $deleteStmt = $conn->prepare($deleteImageSql);
                            $deleteStmt->bind_param("is", $imageId, $roomId);
                            $deleteStmt->execute();
                            $deleteStmt->close();
                        }
                    }
                }
                
                // Handle new image uploads
                if (isset($_FILES['room_images']) && !empty($_FILES['room_images']['name'][0])) {
                    // Get the highest display order for existing images
                    $maxOrderSql = "SELECT COALESCE(MAX(display_order), -1) as max_order FROM room_images WHERE room_id = ?";
                    $maxOrderStmt = $conn->prepare($maxOrderSql);
                    $maxOrderStmt->bind_param("s", $roomId);
                    $maxOrderStmt->execute();
                    $maxOrderResult = $maxOrderStmt->get_result();
                    $maxOrder = $maxOrderResult->fetch_assoc()['max_order'];
                    $maxOrderStmt->close();
                    
                    $totalImages = count($_FILES['room_images']['tmp_name']);
                    $uploadedCount = 0;
                    
                    for ($i = 0; $i < $totalImages; $i++) {
                        if (!empty($_FILES['room_images']['tmp_name'][$i]) && $_FILES['room_images']['error'][$i] === UPLOAD_ERR_OK) {
                            // Check file type
                            $fileType = $_FILES['room_images']['type'][$i];
                            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                            
                            if (!in_array($fileType, $allowedTypes)) {
                                continue;
                            }
                            
                            // Check file size (max 5MB)
                            if ($_FILES['room_images']['size'][$i] > 5 * 1024 * 1024) {
                                continue;
                            }
                            
                            $originalName = $_FILES['room_images']['name'][$i];
                            $imageExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                            
                            // Ensure valid extension
                            if (!in_array($imageExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                                continue;
                            }
                            
                            $displayOrder = $maxOrder + $uploadedCount + 1;
                            $newImageName = $roomId . '_' . time() . '_' . $displayOrder . '.' . $imageExtension;
                            $imagePath = $uploadDir . $newImageName;
                            
                            if (move_uploaded_file($_FILES['room_images']['tmp_name'][$i], $imagePath)) {
                                $imageSql = "INSERT INTO room_images (room_id, image_path, display_order) VALUES (?, ?, ?)";
                                $imageStmt = $conn->prepare($imageSql);
                                
                                if ($imageStmt === false) {
                                    throw new Exception("Error preparing image statement: " . $conn->error);
                                }
                                
                                $imageStmt->bind_param("ssi", $roomId, $imagePath, $displayOrder);
                                
                                if ($imageStmt->execute()) {
                                    $uploadedCount++;
                                } else {
                                    // Delete the uploaded file if database insert fails
                                    if (file_exists($imagePath)) {
                                        unlink($imagePath);
                                    }
                                    throw new Exception("Error inserting image: " . $imageStmt->error);
                                }
                                
                                $imageStmt->close();
                            } else {
                                throw new Exception("Error uploading image: " . $originalName);
                            }
                        }
                    }
                }
                
                // Update display order for existing images (0-based)
                if (isset($_POST['existing_image_order']) && !empty($_POST['existing_image_order'])) {
                    $imageOrder = json_decode($_POST['existing_image_order'], true);
                    
                    if (is_array($imageOrder)) {
                        foreach ($imageOrder as $imageId => $order) {
                            $updateOrderSql = "UPDATE room_images SET display_order = ? WHERE id = ? AND room_id = ?";
                            $updateOrderStmt = $conn->prepare($updateOrderSql);
                            $updateOrderStmt->bind_param("iis", $order, $imageId, $roomId);
                            $updateOrderStmt->execute();
                            $updateOrderStmt->close();
                        }
                    }
                }
                
                $conn->commit();
                $message = "Room updated successfully!";
                $messageType = 'success';
                
                // Redirect to listings page with success message
                header("Location: index.php?tab=listings&updated=1&room_id=" . $roomId);
                exit();
                
            } else {
                throw new Exception("Error updating room: " . $stmt->error);
            }
            $stmt->close();
            
        } catch (Exception $e) {
            $conn->rollback();
            $message = "Error: " . $e->getMessage();
            $messageType = 'danger';
        }
    }
}

// Show success message if redirected after update
if (isset($_GET['updated']) && $_GET['updated'] == '1') {
    $message = "Room updated successfully!";
    $messageType = 'success';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Room Listing</title>
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
        }

        .room-management-container {
            max-width: 1000px;
            margin: 0 auto;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica Neue', Arial, sans-serif;
        }

        .room-card {
            background: var(--pure-white);
            border: 1px solid var(--border-light);
            border-radius: 8px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        .room-card-header {
            background: var(--light-gray);
            border-bottom: 1px solid var(--border-light);
            padding: 1.25rem 1.5rem;
            border-radius: 8px 8px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .room-card-title {
            margin: 0;
            font-weight: 600;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .room-card-title i {
            color: var(--accent-orange);
        }

        .room-card-body {
            padding: 1.5rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            font-weight: 500;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .required-asterisk {
            color: var(--accent-orange);
        }

        .form-control,
        .form-select {
            width: 100%;
            padding: 0.6rem 0.75rem;
            border: 1px solid var(--border-light);
            border-radius: 6px;
            font-size: 0.9rem;
            transition: border-color 0.2s ease;
            background: var(--pure-white);
        }

        .form-control:focus,
        .form-select:focus {
            outline: none;
            border-color: var(--accent-orange);
            box-shadow: 0 0 0 0.2rem rgba(253, 126, 20, 0.15);
        }

        .btn {
            padding: 0.6rem 1.2rem;
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.9rem;
            border: 1px solid;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-block;
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
        }

        .btn-secondary {
            background: var(--text-muted);
            border-color: var(--text-muted);
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            border-color: #5a6268;
            color: white;
        }

        .file-input-wrapper {
            position: relative;
            display: inline-block;
            cursor: pointer;
            border: 2px dashed var(--border-light);
            border-radius: 6px;
            padding: 2rem;
            text-align: center;
            width: 100%;
            transition: border-color 0.2s ease;
            background: var(--pure-white);
        }

        .file-input-wrapper:hover {
            border-color: var(--accent-orange);
        }

        .file-input-wrapper.dragover {
            border-color: var(--accent-orange);
            background-color: rgba(253, 126, 20, 0.05);
        }

        .file-input-wrapper input[type=file] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .image-gallery {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-top: 1rem;
            min-height: 50px;
            padding: 10px;
            border: 2px dashed transparent;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .image-gallery.sortable-drag {
            border-color: var(--accent-orange);
            background-color: rgba(253, 126, 20, 0.05);
        }

        .image-preview {
            position: relative;
            width: 100px;
            height: 100px;
            border-radius: 6px;
            overflow: hidden;
            border: 2px solid var(--border-light);
            cursor: move;
            transition: all 0.3s ease;
        }

        .image-preview:hover {
            transform: scale(1.05);
            border-color: var(--accent-orange);
        }

        .image-preview.dragging {
            opacity: 0.5;
            transform: rotate(5deg);
            border-color: var(--accent-orange);
            box-shadow: 0 5px 15px rgba(253, 126, 20, 0.3);
        }

        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .image-preview .remove-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background: var(--danger-red);
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .image-preview:hover .remove-btn {
            opacity: 1;
        }

        .image-preview .order-badge {
            position: absolute;
            bottom: 5px;
            left: 5px;
            background: var(--accent-orange);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .image-preview .main-badge {
            position: absolute;
            top: 5px;
            left: 5px;
            background: var(--success-green);
            color: white;
            border-radius: 3px;
            padding: 2px 6px;
            font-size: 10px;
            font-weight: bold;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .image-preview:first-child .main-badge {
            opacity: 1;
        }

        .existing-images {
            margin-bottom: 1rem;
        }

        .existing-images h6 {
            margin-bottom: 0.5rem;
            color: var(--text-dark);
            font-weight: 500;
        }

        .upload-info {
            margin-top: 0.5rem;
            font-size: 0.875rem;
            color: var(--text-muted);
        }

        .drag-hint {
            background: rgba(253, 126, 20, 0.1);
            border: 1px solid rgba(253, 126, 20, 0.3);
            border-radius: 6px;
            padding: 0.75rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
            color: var(--text-dark);
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .loading-content {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            text-align: center;
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--accent-orange);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .room-card-header {
                flex-direction: column;
                gap: 0.5rem;
                align-items: flex-start;
            }

            .image-preview {
                width: 80px;
                height: 80px;
            }
        }
    </style>
</head>
<body>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-content">
        <div class="loading-spinner"></div>
        <p>Updating room listing...</p>
    </div>
</div>

<div class="container-fluid room-management-container mt-4">
    
    <!-- Message Alert -->
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : ($messageType === 'danger' ? 'exclamation-triangle' : 'info-circle'); ?> me-2"></i>
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">&times;</button>
        </div>
    <?php endif; ?>

    <?php if ($roomData): ?>
    <!-- Edit Room Form -->
    <div class="room-card">
        <div class="room-card-header">
            <h5 class="room-card-title">
                <i class="fas fa-edit"></i>
                Edit Rent House Listing
            </h5>
            <a href="index.php?tab=listings" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left me-2"></i>
                Back to Listings
            </a>
        </div>
        <div class="room-card-body">
            <form method="post" action="" enctype="multipart/form-data" id="editRoomForm">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">
                            Description<span class="required-asterisk">*</span>
                        </label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Room description" required><?php echo htmlspecialchars($roomData['description']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            Rent Amount<span class="required-asterisk">*</span>
                        </label>
                        <input type="text" name="rent" class="form-control" placeholder="e.g., ₹15,000" value="<?php echo htmlspecialchars($roomData['rent']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            Deposit Amount<span class="required-asterisk">*</span>
                        </label>
                        <input type="text" name="deposit" class="form-control" placeholder="e.g., ₹30,000" value="<?php echo htmlspecialchars($roomData['deposit']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            Availability Date<span class="required-asterisk">*</span>
                        </label>
                        <input type="date" name="availability_date" class="form-control" value="<?php echo htmlspecialchars($roomData['availability_date']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            Room Type<span class="required-asterisk">*</span>
                        </label>
                        <select name="room_type" class="form-select" required>
                            <option value="">Select Type</option>
                            <option value="1BHK" <?php echo ($roomData['room_type'] == '1BHK') ? 'selected' : ''; ?>>1BHK</option>
                            <option value="2BHK" <?php echo ($roomData['room_type'] == '2BHK') ? 'selected' : ''; ?>>2BHK</option>
                            <option value="3BHK" <?php echo ($roomData['room_type'] == '3BHK') ? 'selected' : ''; ?>>3BHK</option>
                            <option value="4+BHK" <?php echo ($roomData['room_type'] == '4+BHK') ? 'selected' : ''; ?>>4+BHK</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            Status<span class="required-asterisk">*</span>
                        </label>
                        <select name="status" class="form-select" required>
                            <option value="">Select Status</option>
                            <option value="available" <?php echo ($roomData['status'] == 'available') ? 'selected' : ''; ?>>Available</option>
                            <option value="occupied" <?php echo ($roomData['status'] == 'occupied') ? 'selected' : ''; ?>>Occupied</option>
                            <option value="maintenance" <?php echo ($roomData['status'] == 'maintenance') ? 'selected' : ''; ?>>Maintenance</option>
                            <option value="hold" <?php echo ($roomData['status'] == 'hold') ? 'selected' : ''; ?>>Hold</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            Furnished<span class="required-asterisk">*</span>
                        </label>
                        <select name="furnished" class="form-select" required>
                            <option value="">Select Option</option>
                            <option value="fully_furnished" <?php echo ($roomData['furnished'] == 'fully_furnished') ? 'selected' : ''; ?>>Fully Furnished</option>
                            <option value="semi_furnished" <?php echo ($roomData['furnished'] == 'semi_furnished') ? 'selected' : ''; ?>>Semi Furnished</option>
                            <option value="unfurnished" <?php echo ($roomData['furnished'] == 'unfurnished') ? 'selected' : ''; ?>>Unfurnished</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            Area<span class="required-asterisk">*</span>
                        </label>
                        <select name="area" class="form-select" required>
                            <option value="">Select Area</option>
                            <option value="Kondapur" <?php echo ($roomData['area'] == 'Kondapur') ? 'selected' : ''; ?>>Kondapur</option>
                            <option value="Gachibowli" <?php echo ($roomData['area'] == 'Gachibowli') ? 'selected' : ''; ?>>Gachibowli</option>
                            <option value="Tellapur" <?php echo ($roomData['area'] == 'Tellapur') ? 'selected' : ''; ?>>Tellapur</option>
                            <option value="Nallagandla" <?php echo ($roomData['area'] == 'Nallagandla') ? 'selected' : ''; ?>>Nallagandla</option>
                            <option value="Masjid banda" <?php echo ($roomData['area'] == 'Masjid banda') ? 'selected' : ''; ?>>Masjid banda</option>
                            <option value="Lingampally" <?php echo ($roomData['area'] == 'Lingampally') ? 'selected' : ''; ?>>Lingampally</option>
                            <option value="Kollur" <?php echo ($roomData['area'] == 'Kollur') ? 'selected' : ''; ?>>Kollur</option>
                            <option value="Miyapur" <?php echo ($roomData['area'] == 'Miyapur') ? 'selected' : ''; ?>>Miyapur</option>
                            <option value="Bachupally" <?php echo ($roomData['area'] == 'Bachupally') ? 'selected' : ''; ?>>Bachupally</option>
                            <option value="Nizampet" <?php echo ($roomData['area'] == 'Nizampet') ? 'selected' : ''; ?>>Nizampet</option>
                            <option value="KPHB" <?php echo ($roomData['area'] == 'KPHB') ? 'selected' : ''; ?>>KPHB</option>
                            <option value="Kukatpally" <?php echo ($roomData['area'] == 'Kukatpally') ? 'selected' : ''; ?>>Kukatpally</option>
                            <option value="Madhapur" <?php echo ($roomData['area'] == 'Madhapur') ? 'selected' : ''; ?>>Madhapur</option>
                            <option value="Hitech City" <?php echo ($roomData['area'] == 'Hitech City') ? 'selected' : ''; ?>>Hitech City</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            Locality<span class="required-asterisk">*</span>
                        </label>
                        <select name="locality" class="form-select" required>
                            <option value="">Select Locality</option>
                            <option value="Raja Rajeswari Nagar" <?php echo ($roomData['locality'] == 'Raja Rajeswari Nagar') ? 'selected' : ''; ?>>Raja Rajeswari Nagar</option>
                            <option value="Raghavendra colony" <?php echo ($roomData['locality'] == 'Raghavendra colony') ? 'selected' : ''; ?>>Raghavendra colony</option>
                            <option value="JV Hills" <?php echo ($roomData['locality'] == 'JV Hills') ? 'selected' : ''; ?>>JV Hills</option>
                            <option value="Gautami Enclave" <?php echo ($roomData['locality'] == 'Gautami Enclave') ? 'selected' : ''; ?>>Gautami Enclave</option>
                            <option value="Jubilee Garden" <?php echo ($roomData['locality'] == 'Jubilee Garden') ? 'selected' : ''; ?>>Jubilee Garden</option>
                            <option value="Golden Tulip colony" <?php echo ($roomData['locality'] == 'Golden Tulip colony') ? 'selected' : ''; ?>>Golden Tulip colony</option>
                            <option value="Shilpa Park" <?php echo ($roomData['locality'] == 'Shilpa Park') ? 'selected' : ''; ?>>Shilpa Park</option>
                            <option value="Prashanth Nagar Colony" <?php echo ($roomData['locality'] == 'Prashanth Nagar Colony') ? 'selected' : ''; ?>>Prashanth Nagar Colony</option>
                            <option value="Whitefields" <?php echo ($roomData['locality'] == 'Whitefields') ? 'selected' : ''; ?>>Whitefields</option>
                            <option value="Safari Nagar" <?php echo ($roomData['locality'] == 'Safari Nagar') ? 'selected' : ''; ?>>Safari Nagar</option>
                            <option value="Police colony" <?php echo ($roomData['locality'] == 'Police colony') ? 'selected' : ''; ?>>Police colony</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Room Images</label>
                    
                    <?php if (!empty($existingImages)): ?>
                    <div class="drag-hint">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>How to reorder:</strong> Drag and drop images to change their order. The first image (order 0) will be the main display image.
                    </div>
                    
                    <div class="existing-images">
                        <h6><i class="fas fa-images me-2"></i>Current Images (Drag to reorder)</h6>
                        <div id="existingImagePreview" class="image-gallery">
                            <?php foreach ($existingImages as $index => $image): ?>
                            <div class="image-preview" data-image-id="<?php echo $image['id']; ?>" draggable="true">
                                <img src="<?php echo htmlspecialchars($image['image_path']); ?>" alt="Room Image <?php echo $index; ?>">
                                <button type="button" class="remove-btn" onclick="removeExistingImage(<?php echo $image['id']; ?>)">
                                    <i class="fas fa-times"></i>
                                </button>
                                <div class="order-badge"><?php echo $index; ?></div>
                                <div class="main-badge">MAIN</div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="file-input-wrapper" id="fileDropZone">
                        <input type="file" name="room_images[]" multiple accept="image/*" id="roomImages">
                        <div>
                            <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                            <p class="mb-0">Click to select new images or drag and drop</p>
                            <small class="text-muted">You can add more images (Max: 5MB each, JPEG/PNG/GIF/WebP)</small>
                        </div>
                    </div>
                    <div class="upload-info">
                        <small>
                            <i class="fas fa-info-circle me-1"></i>
                            Images start from order 0. Drag images to reorder them. The first image (order 0) will be the main display image.
                        </small>
                    </div>
                    <div id="newImagePreview" class="image-gallery"></div>
                    
                    <!-- Hidden inputs for tracking changes -->
                    <input type="hidden" name="deleted_images" id="deletedImages" value="">
                    <input type="hidden" name="existing_image_order" id="existingImageOrder" value="">
                </div>
                
                <div class="mt-4">
                    <button type="submit" name="update_room" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>
                        Update Room
                    </button>
                    <a href="index.php?tab=listings" class="btn btn-secondary ms-2">
                        <i class="fas fa-times me-2"></i>
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
let newImageFiles = [];
let deletedImageIds = [];
let draggedElement = null;
let dragCounter = 0;

// File input and drag-drop functionality
const fileInput = document.getElementById('roomImages');
const dropZone = document.getElementById('fileDropZone');
const newImagePreview = document.getElementById('newImagePreview');
const existingImagePreview = document.getElementById('existingImagePreview');

// Prevent default drag behaviors
['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, preventDefaults, false);
    document.body.addEventListener(eventName, preventDefaults, false);
});

// Highlight drop zone when item is dragged over it
['dragenter', 'dragover'].forEach(eventName => {
    dropZone.addEventListener(eventName, () => dropZone.classList.add('dragover'), false);
});

['dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, () => dropZone.classList.remove('dragover'), false);
});

// Handle dropped files
dropZone.addEventListener('drop', handleDrop, false);

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    handleFiles(files);
}

// File input change event
fileInput.addEventListener('change', function(e) {
    handleFiles(e.target.files);
});

function handleFiles(files) {
    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    const maxSize = 5 * 1024 * 1024; // 5MB
    
    const newFiles = Array.from(files).filter(file => {
        if (!validTypes.includes(file.type)) {
            alert(`Invalid file type: ${file.name}. Please use JPEG, PNG, GIF, or WebP images.`);
            return false;
        }
        if (file.size > maxSize) {
            alert(`File too large: ${file.name}. Maximum size is 5MB.`);
            return false;
        }
        return true;
    });
    
    newImageFiles = [...newImageFiles, ...newFiles];
    updateNewImagePreview();
}

function updateNewImagePreview() {
    newImagePreview.innerHTML = '';
    
    newImageFiles.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'image-preview';
            div.draggable = true;
            div.dataset.index = index;
            div.innerHTML = `
                <img src="${e.target.result}" alt="New Preview ${index}">
                <button type="button" class="remove-btn" onclick="removeNewImage(${index})">
                    <i class="fas fa-times"></i>
                </button>
                <div class="order-badge">+${index}</div>
                ${index === 0 && getExistingImageCount() === 0 ? '<div class="main-badge">MAIN</div>' : ''}
            `;
            
            // Add drag event listeners
            div.addEventListener('dragstart', handleDragStart);
            div.addEventListener('dragend', handleDragEnd);
            
            newImagePreview.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
    
    updateFileInput();
}

function getExistingImageCount() {
    return existingImagePreview ? existingImagePreview.querySelectorAll('.image-preview').length : 0;
}

function removeNewImage(index) {
    newImageFiles.splice(index, 1);
    updateNewImagePreview();
}

function removeExistingImage(imageId) {
    if (confirm('Are you sure you want to delete this image?')) {
        deletedImageIds.push(imageId);
        document.getElementById('deletedImages').value = JSON.stringify(deletedImageIds);
        
        // Remove from DOM
        const imageElement = document.querySelector(`[data-image-id="${imageId}"]`);
        if (imageElement) {
            imageElement.remove();
        }
        
        updateExistingImageOrder();
        updateMainBadges();
    }
}

function updateFileInput() {
    const dt = new DataTransfer();
    newImageFiles.forEach(file => dt.items.add(file));
    fileInput.files = dt.files;
}

function updateExistingImageOrder() {
    const existingImages = existingImagePreview?.querySelectorAll('.image-preview');
    const order = {};
    
    if (existingImages) {
        existingImages.forEach((img, index) => {
            const imageId = img.dataset.imageId;
            if (imageId) {
                order[imageId] = index; // 0-based ordering
                // Update visual order badge
                const badge = img.querySelector('.order-badge');
                if (badge) {
                    badge.textContent = index;
                }
            }
        });
    }
    
    document.getElementById('existingImageOrder').value = JSON.stringify(order);
}

function updateMainBadges() {
    // Remove all main badges first
    document.querySelectorAll('.main-badge').forEach(badge => badge.style.display = 'none');
    
    // Add main badge to first image (order 0)
    const firstExistingImage = existingImagePreview?.querySelector('.image-preview:first-child .main-badge');
    const firstNewImage = newImagePreview?.querySelector('.image-preview:first-child .main-badge');
    
    if (firstExistingImage) {
        firstExistingImage.style.display = 'flex';
    } else if (firstNewImage && getExistingImageCount() === 0) {
        firstNewImage.style.display = 'flex';
    }
}

// Initialize existing image drag and drop
if (existingImagePreview) {
    existingImagePreview.addEventListener('dragstart', handleDragStart);
    existingImagePreview.addEventListener('dragover', handleDragOver);
    existingImagePreview.addEventListener('drop', handleDropImage);
    existingImagePreview.addEventListener('dragenter', handleDragEnter);
    existingImagePreview.addEventListener('dragleave', handleDragLeave);
}

// Initialize new image drag and drop
if (newImagePreview) {
    newImagePreview.addEventListener('dragstart', handleDragStart);
    newImagePreview.addEventListener('dragover', handleDragOver);
    newImagePreview.addEventListener('drop', handleDropImage);
    newImagePreview.addEventListener('dragenter', handleDragEnter);
    newImagePreview.addEventListener('dragleave', handleDragLeave);
}

function handleDragStart(e) {
    if (!e.target.classList.contains('image-preview')) return;
    
    draggedElement = e.target;
    e.target.classList.add('dragging');
    
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/html', e.target.outerHTML);
    
    // Add visual feedback
    setTimeout(() => {
        e.target.style.opacity = '0.5';
    }, 0);
}

function handleDragEnd(e) {
    if (e.target.classList.contains('image-preview')) {
        e.target.classList.remove('dragging');
        e.target.style.opacity = '';
    }
    
    // Clean up
    draggedElement = null;
    
    // Remove drag styling from galleries
    document.querySelectorAll('.image-gallery').forEach(gallery => {
        gallery.classList.remove('sortable-drag');
    });
}

function handleDragOver(e) {
    if (e.preventDefault) {
        e.preventDefault();
    }
    
    e.dataTransfer.dropEffect = 'move';
    return false;
}

function handleDragEnter(e) {
    dragCounter++;
    if (e.target.classList.contains('image-gallery')) {
        e.target.classList.add('sortable-drag');
    }
}

function handleDragLeave(e) {
    dragCounter--;
    if (dragCounter === 0 && e.target.classList.contains('image-gallery')) {
        e.target.classList.remove('sortable-drag');
    }
}

function handleDropImage(e) {
    if (e.stopPropagation) {
        e.stopPropagation();
    }
    
    dragCounter = 0;
    
    if (!draggedElement) return false;
    
    const dropTarget = e.target.closest('.image-preview');
    const gallery = e.target.closest('.image-gallery');
    
    if (gallery) {
        gallery.classList.remove('sortable-drag');
    }
    
    if (dropTarget && dropTarget !== draggedElement) {
        const allImages = Array.from(gallery.querySelectorAll('.image-preview'));
        const draggedIndex = allImages.indexOf(draggedElement);
        const targetIndex = allImages.indexOf(dropTarget);
        
        if (draggedIndex > targetIndex) {
            gallery.insertBefore(draggedElement, dropTarget);
        } else {
            gallery.insertBefore(draggedElement, dropTarget.nextSibling);
        }
        
        // Update order after reordering
        if (gallery === existingImagePreview) {
            updateExistingImageOrder();
        } else if (gallery === newImagePreview) {
            reorderNewImages();
        }
        
        // Update main badges
        updateMainBadges();
    }
    
    // Clean up
    if (draggedElement) {
        draggedElement.classList.remove('dragging');
        draggedElement.style.opacity = '';
        draggedElement = null;
    }
    
    return false;
}

function reorderNewImages() {
    const imageElements = newImagePreview.querySelectorAll('.image-preview');
    const reorderedFiles = [];
    
    imageElements.forEach((element, newIndex) => {
        const oldIndex = parseInt(element.dataset.index);
        reorderedFiles.push(newImageFiles[oldIndex]);
        
        // Update visual order badge
        const badge = element.querySelector('.order-badge');
        if (badge) {
            badge.textContent = `+${newIndex}`;
        }
        
        // Update dataset index
        element.dataset.index = newIndex;
        
        // Update remove button onclick
        const removeBtn = element.querySelector('.remove-btn');
        if (removeBtn) {
            removeBtn.onclick = () => removeNewImage(newIndex);
        }
    });
    
    newImageFiles = reorderedFiles;
    updateFileInput();
}

// Form validation and submission
document.getElementById('editRoomForm').addEventListener('submit', function(e) {
    const requiredFields = this.querySelectorAll('[required]');
    let hasError = false;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.style.borderColor = '#dc3545';
            hasError = true;
        } else {
            field.style.borderColor = '#e9ecef';
        }
    });
    
    if (hasError) {
        e.preventDefault();
        alert('Please fill in all required fields.');
        return;
    }
    
    // Show loading overlay
    document.getElementById('loadingOverlay').style.display = 'flex';
    
    // Update hidden fields before submission
    document.getElementById('deletedImages').value = JSON.stringify(deletedImageIds);
    updateExistingImageOrder();
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Set initial order for existing images (0-based)
    updateExistingImageOrder();
    updateMainBadges();
    
    // Add drag event listeners to existing images
    const existingImages = existingImagePreview?.querySelectorAll('.image-preview');
    if (existingImages) {
        existingImages.forEach(img => {
            img.addEventListener('dragstart', handleDragStart);
            img.addEventListener('dragend', handleDragEnd);
        });
    }
    
    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert.close();
        }, 5000);
    });
});

// Add global dragend event listener
document.addEventListener('dragend', handleDragEnd);
</script>

<?php
$conn->close();
?>

</body>
</html>
