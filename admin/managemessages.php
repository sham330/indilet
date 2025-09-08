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

// Handle status update via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    header('Content-Type: application/json');
    
    $messageId = intval($_POST['message_id']);
    $newStatus = trim($_POST['status']);
    
    // Validate status
    $validStatuses = ['pending', 'ongoing', 'completed', 'wrong_data'];
    if (!in_array($newStatus, $validStatuses)) {
        echo json_encode(['success' => false, 'error' => 'Invalid status']);
        exit();
    }
    
    try {
        $sql = "UPDATE messages SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $newStatus, $messageId);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Message not found or no changes made']);
        }
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit();
}

// Handle Delete Message
if (isset($_GET['delete_message']) && function_exists('isAdmin') && isAdmin()) {
    $messageId = intval($_GET['delete_message']);
    
    try {
        $sql = "DELETE FROM messages WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $messageId);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            header("Location: index.php?tab=messages&message=" . urlencode("Message deleted successfully!") . "&type=success");
            exit();
        } else {
            throw new Exception("Message not found!");
        }
        $stmt->close();
        
    } catch (Exception $e) {
        header("Location: index.php?tab=messages&message=" . urlencode($e->getMessage()) . "&type=error");
        exit();
    }
}

// Get search and filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : '';

// Build the SQL query with search and filters
$sql = "SELECT * FROM messages";
$whereConditions = [];
$params = [];
$paramTypes = '';

// Add search condition
if (!empty($search)) {
    $whereConditions[] = "(name LIKE ? OR email LIKE ? OR message LIKE ? OR phonenumber LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
    $paramTypes .= 'ssss';
}

// Add status filter
if (!empty($statusFilter)) {
    $whereConditions[] = "status = ?";
    $params[] = $statusFilter;
    $paramTypes .= 's';
}

// Add WHERE clause if there are conditions
if (!empty($whereConditions)) {
    $sql .= " WHERE " . implode(" AND ", $whereConditions);
}

$sql .= " ORDER BY created_at DESC";

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
    <title>Message Management Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-orange: #ff6b35;
            --secondary-orange: #ff8c42;
            --light-orange: #fff4f1;
            --dark-orange: #e55a2b;
            --text-primary: #2c3e50;
            --text-secondary: #6c757d;
            --bg-light: #f8fafc;
            --border-light: #e2e8f0;
            --shadow-sm: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }

        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        body { 
            background-color: var(--bg-light);
            color: var(--text-primary);
            line-height: 1.6;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-orange) 0%, var(--secondary-orange) 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 1rem 1rem;
            box-shadow: var(--shadow-lg);
        }

        .page-title {
            font-weight: 700;
            font-size: 2rem;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .search-card {
            background: white;
            border: 1px solid var(--border-light);
            border-radius: 0.75rem;
            box-shadow: var(--shadow-sm);
            margin-bottom: 2rem;
            transition: all 0.2s ease;
        }

        .search-card:hover {
            box-shadow: var(--shadow-md);
        }

        .btn-orange {
            background: var(--primary-orange);
            border: 1px solid var(--primary-orange);
            color: white;
            font-weight: 500;
            padding: 0.5rem 1.5rem;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }

        .btn-orange:hover {
            background: var(--dark-orange);
            border-color: var(--dark-orange);
            color: white;
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .btn-outline-orange {
            background: transparent;
            border: 1px solid var(--primary-orange);
            color: var(--primary-orange);
            font-weight: 500;
            padding: 0.5rem 1.5rem;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }

        .btn-outline-orange:hover {
            background: var(--primary-orange);
            border-color: var(--primary-orange);
            color: white;
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .message-card {
            background: white;
            border: 1px solid var(--border-light);
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            overflow: hidden;
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
        }

        .message-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        .message-header {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-bottom: 1px solid var(--border-light);
            padding: 1rem 1.5rem;
        }

        .message-title {
            font-weight: 600;
            font-size: 1.125rem;
            margin: 0;
            color: var(--text-primary);
        }

        .status-dropdown {
            position: relative;
            display: inline-block;
        }

        .status-badge {
            padding: 0.375rem 0.875rem;
            border-radius: 2rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            outline: none;
            user-select: none;
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-badge::after {
            content: '\f0d7';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: 0.65rem;
            opacity: 0.7;
        }

        .status-badge:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-sm);
        }

        .status-badge.pending {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: white;
        }

        .status-badge.ongoing {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
        }

        .status-badge.completed {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }

        .status-badge.wrong_data {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }

        .status-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }

        .status-modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 0;
            border-radius: 1rem;
            width: 400px;
            box-shadow: var(--shadow-lg);
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-header-custom {
            background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
            color: white;
            padding: 1.25rem 1.5rem;
            border-radius: 1rem 1rem 0 0;
        }

        .modal-body-custom {
            padding: 1.5rem;
        }

        .status-option {
            display: block;
            width: 100%;
            padding: 0.75rem 1rem;
            margin-bottom: 0.5rem;
            border: 1px solid var(--border-light);
            border-radius: 0.5rem;
            background: white;
            color: var(--text-primary);
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            font-weight: 500;
        }

        .status-option:hover {
            background: var(--light-orange);
            border-color: var(--primary-orange);
            color: var(--primary-orange);
            transform: translateX(4px);
        }

        .form-control {
            border: 1px solid var(--border-light);
            border-radius: 0.5rem;
            padding: 0.625rem 0.875rem;
            transition: all 0.2s ease;
            font-weight: 400;
        }

        .form-control:focus {
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
        }

        .form-select {
            border: 1px solid var(--border-light);
            border-radius: 0.5rem;
            padding: 0.625rem 0.875rem;
            transition: all 0.2s ease;
        }

        .form-select:focus {
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
        }

        .message-content {
            background: var(--bg-light);
            border: 1px solid var(--border-light);
            border-radius: 0.5rem;
            padding: 1rem;
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .info-label {
            font-weight: 600;
            color: var(--text-secondary);
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .info-value {
            color: var(--text-primary);
            margin-bottom: 1rem;
        }

        .btn-delete {
            background: transparent;
            border: 1px solid #dc3545;
            color: #dc3545;
            font-weight: 500;
            padding: 0.4rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }

        .btn-delete:hover {
            background: #dc3545;
            color: white;
            transform: translateY(-1px);
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 0.75rem;
            border: 1px solid var(--border-light);
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--text-secondary);
            margin-bottom: 1rem;
        }

        .alert {
            border-radius: 0.75rem;
            border: none;
            font-weight: 500;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: white;
            cursor: pointer;
            float: right;
            margin-top: -0.25rem;
        }
    </style>
</head>
<body>

<!-- Message Alert -->
<?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo htmlspecialchars($messageType); ?> alert-dismissible fade show m-3" role="alert">
        <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>



<div class="container">
    <!-- Search and Filter -->
    <div class="search-card">
        <div class="card-body p-4">
<form method="GET" action="index.php">
    <input type="hidden" name="tab" value="messages">                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold text-secondary">Search Messages</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0">
                                <i class="fas fa-search text-secondary"></i>
                            </span>
                            <input type="text" name="search" class="form-control border-start-0" 
                                   placeholder="Search by name, email, phone, or message content..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-secondary">Filter by Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>
                                <i class="fas fa-clock"></i> Pending
                            </option>
                            <option value="ongoing" <?php echo $statusFilter === 'ongoing' ? 'selected' : ''; ?>>
                                <i class="fas fa-spinner"></i> Ongoing
                            </option>
                            <option value="completed" <?php echo $statusFilter === 'completed' ? 'selected' : ''; ?>>
                                <i class="fas fa-check"></i> Completed
                            </option>
                            <option value="wrong_data" <?php echo $statusFilter === 'wrong_data' ? 'selected' : ''; ?>>
                                <i class="fas fa-times"></i> Wrong Data
                            </option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-orange">
                            <i class="fas fa-search me-2"></i>Search
                        </button>
                        <a href="managemessages.php" class="btn btn-outline-orange">
                            <i class="fas fa-refresh me-2"></i>Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Messages -->
    <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <div class="message-card">
                <div class="message-header d-flex justify-content-between align-items-center">
                    <h5 class="message-title">
                        <i class="fas fa-user me-2 text-secondary"></i>
                        <?php echo htmlspecialchars($row['name']); ?>
                    </h5>
                    <div class="status-dropdown">
                        <button class="status-badge <?php echo htmlspecialchars($row['status']); ?>" 
                                onclick="openStatusModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['status']); ?>')">
                            <?php 
                                $statusIcons = [
                                    'pending' => 'clock',
                                    'ongoing' => 'spinner',
                                    'completed' => 'check',
                                    'wrong_data' => 'times'
                                ];
                                $statusIcon = $statusIcons[$row['status']] ?? 'question';
                            ?>
                            <i class="fas fa-<?php echo $statusIcon; ?>"></i>
                            <?php echo ucwords(str_replace('_', ' ', $row['status'])); ?>
                        </button>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="info-label">Email Address</div>
                            <div class="info-value">
                                <i class="fas fa-envelope text-secondary me-2"></i>
                                <?php echo htmlspecialchars($row['email']); ?>
                            </div>
                        </div>
                        <?php if (!empty($row['phonenumber'])): ?>
                        <div class="col-md-6">
                            <div class="info-label">Phone Number</div>
                            <div class="info-value">
                                <i class="fas fa-phone text-secondary me-2"></i>
                                <?php echo htmlspecialchars($row['phonenumber']); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <div class="info-label">Date Received</div>
                        <div class="info-value">
                            <i class="fas fa-calendar text-secondary me-2"></i>
                            <?php echo date('F j, Y \a\t g:i A', strtotime($row['created_at'])); ?>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="info-label">Message Content</div>
                        <div class="message-content">
                            <?php echo nl2br(htmlspecialchars($row['message'])); ?>
                        </div>
                    </div>
                    
                    <?php if (function_exists('isAdmin') && isAdmin()): ?>
                        <div class="mt-4 pt-3 border-top">
                            <button onclick="confirmDelete(<?php echo $row['id']; ?>)" 
                                    class="btn btn-delete">
                                <i class="fas fa-trash me-2"></i>Delete Message
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h3 class="text-secondary mb-2">No Messages Found</h3>
            <p class="text-muted">There are no messages matching your search criteria.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Status Change Modal -->
<div id="statusModal" class="status-modal">
    <div class="status-modal-content">
        <div class="modal-header-custom">
            <h4 class="mb-0">
                <i class="fas fa-edit me-2"></i>Update Message Status
            </h4>
            <button class="close-modal" onclick="closeStatusModal()">&times;</button>
        </div>
        <div class="modal-body-custom">
            <p class="text-secondary mb-3">Select a new status for this message:</p>
            <div id="statusOptions"></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
let currentMessageId = null;

function openStatusModal(messageId, currentStatus) {
    currentMessageId = messageId;
    const modal = document.getElementById('statusModal');
    const optionsContainer = document.getElementById('statusOptions');
    
    const statuses = [
        {value: 'pending', label: 'Pending', icon: 'clock'},
        {value: 'ongoing', label: 'Ongoing', icon: 'spinner'},
        {value: 'completed', label: 'Completed', icon: 'check'},
        {value: 'wrong_data', label: 'Wrong Data', icon: 'times'}
    ];
    
    let optionsHTML = '';
    statuses.forEach(status => {
        if (status.value !== currentStatus) {
            optionsHTML += `
                <button class="status-option" onclick="updateStatus('${status.value}')">
                    <i class="fas fa-${status.icon} me-3"></i>
                    Change to ${status.label}
                </button>
            `;
        }
    });
    
    optionsContainer.innerHTML = optionsHTML;
    modal.style.display = 'block';
}

function closeStatusModal() {
    document.getElementById('statusModal').style.display = 'none';
    currentMessageId = null;
}

function updateStatus(newStatus) {
    if (!currentMessageId) return;
    
    const formData = new FormData();
    formData.append('action', 'update_status');
    formData.append('message_id', currentMessageId);
    formData.append('status', newStatus);
    
    fetch('managemessages.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeStatusModal();
            location.reload();
        } else {
            alert('Error updating status: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}

function confirmDelete(messageId) {
    if (confirm('Are you sure you want to delete this message? This action cannot be undone.')) {
        window.location.href = `managemessages.php?delete_message=${messageId}`;
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('statusModal');
    if (event.target === modal) {
        closeStatusModal();
    }
}

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeStatusModal();
    }
});
</script>

<?php
$stmt->close();
$conn->close();
?>
</body>
</html>