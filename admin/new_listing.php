<?php
// Include authentication check
require_once 'auth_check.php';


// Include DB connection
include 'connection.php';

// Initialize message variable
$message = '';
$messageType = 'info';

// Create uploads directory if it doesn't exist
$uploadDir = 'uploads/rooms/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Handle Add Room
if (isset($_POST['add_room'])) {
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
        $roomId = uniqid("room_");
        
        try {
            // Start transaction
            $conn->begin_transaction();
            
            // Insert room (added locality field)
            $sql = "INSERT INTO rooms (id, description, rent, deposit, availability_date, room_type, status, furnished, area, locality) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssssss", $roomId, $description, $rent, $deposit, $availability_date, $room_type, $status, $furnished, $area, $locality);
            
            if ($stmt->execute()) {
                // Handle image uploads
                if (!empty($_FILES['room_images']['name'][0])) {
                    $imageOrder = 0;
                    foreach ($_FILES['room_images']['tmp_name'] as $key => $tmpName) {
                        if (!empty($tmpName)) {
                            $originalName = $_FILES['room_images']['name'][$key];
                            $imageExtension = pathinfo($originalName, PATHINFO_EXTENSION);
                            $newImageName = $roomId . '_' . $imageOrder . '.' . $imageExtension;
                            $imagePath = $uploadDir . $newImageName;
                            
                            if (move_uploaded_file($tmpName, $imagePath)) {
                                $imageSql = "INSERT INTO room_images (room_id, image_path, display_order) VALUES (?, ?, ?)";
                                $imageStmt = $conn->prepare($imageSql);
                                $imageStmt->bind_param("ssi", $roomId, $imagePath, $imageOrder);
                                $imageStmt->execute();
                                $imageStmt->close();
                                $imageOrder++;
                            }
                        }
                    }
                }
                
                $conn->commit();
                $message = "Room added successfully!";
                $messageType = 'success';
                
                // Redirect to listing page after 2 seconds
                echo "<script>
                    setTimeout(function() {
                        window.location.href = 'index.php?tab=listings';
                    }, 2000);
                </script>";
                
            } else {
                throw new Exception("Error adding room: " . $stmt->error);
            }
            $stmt->close();
            
        } catch (Exception $e) {
            $conn->rollback();
            $message = $e->getMessage();
            $messageType = 'danger';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Room Listing</title>
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
        }

        .file-input-wrapper:hover {
            border-color: var(--accent-orange);
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
        }

        .image-preview {
            position: relative;
            width: 80px;
            height: 80px;
            border-radius: 6px;
            overflow: hidden;
            border: 2px solid var(--border-light);
        }

        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
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
        }
    </style>
</head>
<body>

<div class="container-fluid room-management-container mt-4">
    
    <!-- Message Alert -->
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : ($messageType === 'danger' ? 'exclamation-triangle' : 'info-circle'); ?> me-2"></i>
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">&times;</button>
        </div>
    <?php endif; ?>

    <!-- Add Room Form -->
    <div class="room-card">
        <div class="room-card-header">
            <h5 class="room-card-title">
                <i class="fas fa-plus-circle"></i>
                Add New Rent house Listing
            </h5>
            <a href="listing.php" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left me-2"></i>
                Back to Listings
            </a>
        </div>
        <div class="room-card-body">
            <form method="post" action="" enctype="multipart/form-data" id="addRoomForm">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">
                            Description<span class="required-asterisk">*</span>
                        </label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Room description" required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            Rent Amount<span class="required-asterisk">*</span>
                        </label>
                        <input type="text" name="rent" class="form-control" placeholder="e.g., ₹15,000" value="<?php echo isset($_POST['rent']) ? htmlspecialchars($_POST['rent']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            Deposit Amount<span class="required-asterisk">*</span>
                        </label>
                        <input type="text" name="deposit" class="form-control" placeholder="e.g., ₹30,000" value="<?php echo isset($_POST['deposit']) ? htmlspecialchars($_POST['deposit']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            Availability Date<span class="required-asterisk">*</span>
                        </label>
                        <input type="date" name="availability_date" class="form-control" value="<?php echo isset($_POST['availability_date']) ? htmlspecialchars($_POST['availability_date']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            Room Type<span class="required-asterisk">*</span>
                        </label>
                        <select name="room_type" class="form-select" required>
                            <option value="">Select Type</option>
                            <option value="1BHK" <?php echo (isset($_POST['room_type']) && $_POST['room_type'] == '1BHK') ? 'selected' : ''; ?>>1BHK</option>
                            <option value="2BHK" <?php echo (isset($_POST['room_type']) && $_POST['room_type'] == '2BHK') ? 'selected' : ''; ?>>2BHK</option>
                            <option value="3BHK" <?php echo (isset($_POST['room_type']) && $_POST['room_type'] == '3BHK') ? 'selected' : ''; ?>>3BHK</option>
                            <option value="4+BHK" <?php echo (isset($_POST['room_type']) && $_POST['room_type'] == '4+BHK') ? 'selected' : ''; ?>>4+BHK</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            Status<span class="required-asterisk">*</span>
                        </label>
                        <select name="status" class="form-select" required>
                            <option value="">Select Status</option>
                            <option value="available" <?php echo (isset($_POST['status']) && $_POST['status'] == 'available') ? 'selected' : ''; ?>>Available</option>
                            <option value="occupied" <?php echo (isset($_POST['status']) && $_POST['status'] == 'occupied') ? 'selected' : ''; ?>>Occupied</option>
                            <option value="maintenance" <?php echo (isset($_POST['status']) && $_POST['status'] == 'maintenance') ? 'selected' : ''; ?>>Maintenance</option>
                            <option value="hold" <?php echo (isset($_POST['status']) && $_POST['status'] == 'hold') ? 'selected' : ''; ?>>Hold</option>

                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            Furnished<span class="required-asterisk">*</span>
                        </label>
                        <select name="furnished" class="form-select" required>
                            <option value="">Select Option</option>
                            <option value="fully_furnished" <?php echo (isset($_POST['furnished']) && $_POST['furnished'] == 'fully_furnished') ? 'selected' : ''; ?>>Fully Furnished</option>
                            <option value="semi_furnished" <?php echo (isset($_POST['furnished']) && $_POST['furnished'] == 'semi_furnished') ? 'selected' : ''; ?>>Semi Furnished</option>
                            <option value="unfurnished" <?php echo (isset($_POST['furnished']) && $_POST['furnished'] == 'unfurnished') ? 'selected' : ''; ?>>Unfurnished</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            Area<span class="required-asterisk">*</span>
                        </label>
                        <select name="area" class="form-select" required>
                            <option value="">Select Area</option>
                           <option value="Kondapur" <?php echo (isset($_POST['area']) && $_POST['area'] == 'Kondapur') ? 'selected' : ''; ?>>Kondapur</option>
<option value="Gachibowli" <?php echo (isset($_POST['area']) && $_POST['area'] == 'Gachibowli') ? 'selected' : ''; ?>>Gachibowli</option>
<option value="Tellapur" <?php echo (isset($_POST['area']) && $_POST['area'] == 'Tellapur') ? 'selected' : ''; ?>>Tellapur</option>
<option value="Nallagandla" <?php echo (isset($_POST['area']) && $_POST['area'] == 'Nallagandla') ? 'selected' : ''; ?>>Nallagandla</option>
<option value="Masjid banda" <?php echo (isset($_POST['area']) && $_POST['area'] == 'Masjid banda') ? 'selected' : ''; ?>>Masjid banda</option>
<option value="Lingampally" <?php echo (isset($_POST['area']) && $_POST['area'] == 'Lingampally') ? 'selected' : ''; ?>>Lingampally</option>
<option value="Kollur" <?php echo (isset($_POST['area']) && $_POST['area'] == 'Kollur') ? 'selected' : ''; ?>>Kollur</option>
<option value="Miyapur" <?php echo (isset($_POST['area']) && $_POST['area'] == 'Miyapur') ? 'selected' : ''; ?>>Miyapur</option>
<option value="Bachupally" <?php echo (isset($_POST['area']) && $_POST['area'] == 'Bachupally') ? 'selected' : ''; ?>>Bachupally</option>
<option value="Nizampet" <?php echo (isset($_POST['area']) && $_POST['area'] == 'Nizampet') ? 'selected' : ''; ?>>Nizampet</option>
<option value="KPHB" <?php echo (isset($_POST['area']) && $_POST['area'] == 'KPHB') ? 'selected' : ''; ?>>KPHB</option>
<option value="Kukatpally" <?php echo (isset($_POST['area']) && $_POST['area'] == 'Kukatpally') ? 'selected' : ''; ?>>Kukatpally</option>
<option value="Madhapur" <?php echo (isset($_POST['area']) && $_POST['area'] == 'Madhapur') ? 'selected' : ''; ?>>Madhapur</option>
<option value="Hitech City" <?php echo (isset($_POST['area']) && $_POST['area'] == 'Hitech City') ? 'selected' : ''; ?>>Hitech City</option>
 </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            Locality<span class="required-asterisk">*</span>
                        </label>
                        <select name="locality" class="form-select" required>
                           <option value="">Select Locality</option>
<option value="Raja Rajeswari Nagar" <?php echo (isset($_POST['locality']) && $_POST['locality'] == 'Raja Rajeswari Nagar') ? 'selected' : ''; ?>>Raja Rajeswari Nagar</option>
<option value="Raghavendra colony" <?php echo (isset($_POST['locality']) && $_POST['locality'] == 'Raghavendra colony') ? 'selected' : ''; ?>>Raghavendra colony</option>
<option value="JV Hills" <?php echo (isset($_POST['locality']) && $_POST['locality'] == 'JV Hills') ? 'selected' : ''; ?>>JV Hills</option>
<option value="Gautami Enclave" <?php echo (isset($_POST['locality']) && $_POST['locality'] == 'Gautami Enclave') ? 'selected' : ''; ?>>Gautami Enclave</option>
<option value="Jubilee Garden" <?php echo (isset($_POST['locality']) && $_POST['locality'] == 'Jubilee Garden') ? 'selected' : ''; ?>>Jubilee Garden</option>
<option value="Golden Tulip colony" <?php echo (isset($_POST['locality']) && $_POST['locality'] == 'Golden Tulip colony') ? 'selected' : ''; ?>>Golden Tulip colony</option>
<option value="Shilpa Park" <?php echo (isset($_POST['locality']) && $_POST['locality'] == 'Shilpa Park') ? 'selected' : ''; ?>>Shilpa Park</option>
<option value="Prashanth Nagar Colony" <?php echo (isset($_POST['locality']) && $_POST['locality'] == 'Prashanth Nagar Colony') ? 'selected' : ''; ?>>Prashanth Nagar Colony</option>
<option value="Whitefields" <?php echo (isset($_POST['locality']) && $_POST['locality'] == 'Whitefields') ? 'selected' : ''; ?>>Whitefields</option>
<option value="Safari Nagar" <?php echo (isset($_POST['locality']) && $_POST['locality'] == 'Safari Nagar') ? 'selected' : ''; ?>>Safari Nagar</option>
<option value="Police colony" <?php echo (isset($_POST['locality']) && $_POST['locality'] == 'Police colony') ? 'selected' : ''; ?>>Police colony</option>
  </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Room Images</label>
                    <div class="file-input-wrapper">
                        <input type="file" name="room_images[]" multiple accept="image/*" id="roomImages">
                        <div>
                            <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                            <p class="mb-0">Click to select images or drag and drop</p>
                            <small class="text-muted">You can select multiple images</small>
                        </div>
                    </div>
                    <div id="imagePreview" class="image-gallery"></div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" name="add_room" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        Add Home
                    </button>
                    <a href="listing.php" class="btn btn-secondary ms-2">
                        <i class="fas fa-times me-2"></i>
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// File upload preview
document.getElementById('roomImages')?.addEventListener('change', function(e) {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    
    Array.from(e.target.files).forEach((file, index) => {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'image-preview';
                div.innerHTML = `<img src="${e.target.result}" alt="Preview ${index + 1}">`;
                preview.appendChild(div);
            };
            reader.readAsDataURL(file);
        }
    });
});

// Form validation
document.getElementById('addRoomForm')?.addEventListener('submit', function(e) {
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
    }
});

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
</script>

<?php
$conn->close();
?>

</body>
</html>
