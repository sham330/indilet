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

// Handle Add User (only admins can add users)
if (isset($_POST['add_user']) && isAdmin()) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);
    
    // Validate inputs
    if (empty($username) || empty($password) || empty($role)) {
        $message = "All fields are required!";
        $messageType = 'danger';
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters long!";
        $messageType = 'danger';
    } else {
        // Check if username already exists
        $checkSql = "SELECT COUNT(*) as count FROM admin WHERE Username = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("s", $username);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['count'] > 0) {
            $message = "Username already exists!";
            $messageType = 'warning';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $id = uniqid("admin_");

            $sql = "INSERT INTO admin (id, Username, password, Role) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $id, $username, $hashedPassword, $role);

            if ($stmt->execute()) {
                $message = "Admin added successfully!";
                $messageType = 'success';
            } else {
                $message = "Error: " . $stmt->error;
                $messageType = 'danger';
            }
            $stmt->close();
        }
        $checkStmt->close();
    }
} elseif (isset($_POST['add_user']) && !isAdmin()) {
    $message = "Access denied! Only administrators can add users.";
    $messageType = 'danger';
}

// Handle Delete User (only admins can delete users)
if (isset($_GET['delete']) && isAdmin()) {
    $deleteId = trim($_GET['delete']);
    
    // Prevent self-deletion
    if ($deleteId === $current_user['id']) {
        $message = "You cannot delete your own account!";
        $messageType = 'warning';
    } elseif (!empty($deleteId)) {
        $sql = "DELETE FROM admin WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $deleteId);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $message = "User deleted successfully!";
                $messageType = 'success';
            } else {
                $message = "User not found!";
                $messageType = 'warning';
            }
        } else {
            $message = "Error deleting user!";
            $messageType = 'danger';
        }
        $stmt->close();
    }
} elseif (isset($_GET['delete']) && !isAdmin()) {
    $message = "Access denied! Only administrators can delete users.";
    $messageType = 'danger';
}

// Fetch all admins using prepared statement
$sql = "SELECT id, Username, Role FROM admin ORDER BY Username ASC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="admin-management-component">
    <style>
        :root {
            --primary-dark: #212529;
            --secondary-dark: #343a40;
            --accent-orange: #fd7e14;
            --light-orange: #ffc107;
            --pure-white: #ffffff;
            --light-gray: #f8f9fa;
            --medium-gray: #6c757d;
            --border-light: #e9ecef;
            --text-dark: #212529;
            --text-muted: #6c757d;
        }

        .admin-management-component {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica Neue', Arial, sans-serif;
            color: var(--text-dark);
            line-height: 1.6;
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .admin-card {
            background: var(--pure-white);
            border: 1px solid var(--border-light);
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .card-header {
            background-color: var(--light-gray);
            border-bottom: 1px solid var(--border-light);
            padding: 1.5rem;
        }

        .card-header h4 {
            margin: 0;
            font-weight: 600;
            color: var(--text-dark);
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-header h4 i {
            color: var(--accent-orange);
            font-size: 1.1rem;
        }

        .card-body {
            padding: 2rem;
        }

        .user-info-card {
            background-color: var(--light-gray);
            border: 1px solid var(--border-light);
            border-radius: 6px;
            padding: 1.25rem;
            margin-bottom: 2rem;
        }

        .user-info-card h6 {
            color: var(--text-dark);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .user-info-card h6 i {
            color: var(--accent-orange);
        }

        .user-info-card .badge {
            background-color: var(--accent-orange);
            color: white;
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-weight: 500;
        }

        .btn-primary {
            background-color: var(--accent-orange);
            border-color: var(--accent-orange);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.2s ease;
            border: 1px solid transparent;
            text-decoration: none;
            display: inline-block;
            font-size: 0.9rem;
        }

        .btn-primary:hover {
            background-color: #e86b00;
            border-color: #e86b00;
            color: white;
            text-decoration: none;
        }

        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.2s ease;
            border: 1px solid transparent;
            text-decoration: none;
            display: inline-block;
            font-size: 0.85rem;
        }

        .btn-danger:hover {
            background-color: #bb2d3b;
            border-color: #bb2d3b;
            color: white;
            text-decoration: none;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        .required {
            color: var(--accent-orange);
        }

        .form-control, .form-select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-light);
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .form-control:focus, .form-select:focus {
            outline: 0;
            border-color: var(--accent-orange);
            box-shadow: 0 0 0 0.2rem rgba(253, 126, 20, 0.25);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        .alert {
            border: none;
            border-radius: 6px;
            padding: 1rem;
            margin-bottom: 1rem;
            border-left: 4px solid;
        }

        .alert-success {
            background-color: #d1e7dd;
            color: #0a3622;
            border-left-color: #198754;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #58151c;
            border-left-color: #dc3545;
        }

        .alert-warning {
            background-color: #fff3cd;
            color: #664d03;
            border-left-color: #ffc107;
        }

        .alert-info {
            background-color: #d1ecf1;
            color: #055160;
            border-left-color: #0dcaf0;
        }

        .alert-dismissible {
            position: relative;
            padding-right: 3rem;
        }

        .btn-close {
            position: absolute;
            top: 0;
            right: 0;
            z-index: 2;
            padding: 1rem;
            background: none;
            border: none;
            font-size: 1rem;
            cursor: pointer;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }

        .table th,
        .table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border-light);
            vertical-align: middle;
        }

        .table th {
            background-color: var(--secondary-dark);
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .table tbody tr:hover {
            background-color: var(--light-gray);
        }

        .badge {
            font-size: 0.75rem;
            padding: 0.35rem 0.65rem;
            border-radius: 4px;
            font-weight: 500;
        }

        .badge-warning {
            background-color: #ffc107;
            color: var(--text-dark);
        }

        .badge-info {
            background-color: #0dcaf0;
            color: white;
        }

        .badge-secondary {
            background-color: var(--medium-gray);
            color: white;
        }

        .text-center {
            text-align: center;
        }

        .text-muted {
            color: var(--text-muted);
        }

        .access-denied {
            background-color: var(--light-gray);
            border-left: 4px solid var(--accent-orange);
            padding: 1.5rem;
            border-radius: 0 6px 6px 0;
            margin-bottom: 2rem;
        }

        .access-denied h5 {
            margin-bottom: 0.5rem;
            color: var(--text-dark);
        }

        .access-denied p {
            margin: 0;
            color: var(--text-muted);
        }

        code {
            background-color: var(--light-gray);
            color: var(--text-dark);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.85rem;
        }
    </style>

    

    <!-- Message Alert -->
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible" role="alert">
            <?php 
            $icon = '';
            switch($messageType) {
                case 'success': $icon = 'fas fa-check-circle'; break;
                case 'danger': $icon = 'fas fa-exclamation-triangle'; break;
                case 'warning': $icon = 'fas fa-exclamation-circle'; break;
                default: $icon = 'fas fa-info-circle'; break;
            }
            ?>
            <i class="<?php echo $icon; ?>"></i>
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" onclick="this.parentElement.style.display='none'">&times;</button>
        </div>
    <?php endif; ?>

    <!-- Add Admin Form (Only for Admins) -->
    <?php if (isAdmin()): ?>
        <div class="admin-card">
            <div class="card-header">
                <h4>
                    <i class="fas fa-user-plus"></i>
                    Add New Administrator
                </h4>
            </div>
            <div class="card-body">
                <form method="post" action="" id="addAdminForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                Username <span class="required">*</span>
                            </label>
                            <input type="text" name="username" class="form-control" 
                                   placeholder="Enter username" required 
                                   pattern="[a-zA-Z0-9_]{3,20}" 
                                   title="Username must be 3-20 characters, letters, numbers, and underscores only">
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                Password <span class="required">*</span>
                            </label>
                            <input type="password" name="password" class="form-control" 
                                   placeholder="Minimum 6 characters" required 
                                   minlength="6">
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                Role <span class="required">*</span>
                            </label>
                            <select name="role" class="form-select" required>
                                <option value="">Select Role</option>
                                <option value="admin">Admin</option>
                                <option value="user">User</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" name="add_user" class="btn-primary">
                        <i class="fas fa-plus"></i>
                        Add Administrator
                    </button>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="access-denied">
            <h5><i class="fas fa-exclamation-triangle"></i> Limited Access</h5>
            <p>You have <strong>User</strong> level access. Only administrators can add or delete users.</p>
        </div>
    <?php endif; ?>

    <!-- Admin Users Table -->
    <div class="admin-card">
        <div class="card-header">
            <h4>
                <i class="fas fa-list"></i>
                Administrator List
                <span class="badge badge-secondary"><?php echo $result->num_rows; ?> Total</span>
            </h4>
        </div>
        <div class="card-body">
            <?php if ($result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-id-card"></i> ID</th>
                                <th><i class="fas fa-user"></i> Username</th>
                                <th><i class="fas fa-shield-alt"></i> Role</th>
                                <th class="text-center"><i class="fas fa-cogs"></i> Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <code><?php echo htmlspecialchars($row['id']); ?></code>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['Username']); ?></strong>
                                    <?php if ($row['id'] === $current_user['id']): ?>
                                        <span class="badge badge-info">You</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo $row['Role'] === 'admin' ? 'badge-warning' : 'badge-info'; ?>">
                                        <i class="fas fa-<?php echo $row['Role'] === 'admin' ? 'crown' : 'user'; ?>"></i>
                                        <?php echo htmlspecialchars($row['Role']); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if (isAdmin() && $row['id'] !== $current_user['id']): ?>
                                        <a href="?delete=<?php echo urlencode($row['id']); ?>" 
                                           class="btn-danger" 
                                           onclick="return confirm('Are you sure you want to delete user: <?php echo htmlspecialchars($row['Username']); ?>?')"
                                           title="Delete User">
                                            <i class="fas fa-trash"></i>
                                            Delete
                                        </a>
                                    <?php elseif ($row['id'] === $current_user['id']): ?>
                                        <span class="text-muted">
                                            <i class="fas fa-user-shield"></i>
                                            Current User
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">
                                            <i class="fas fa-lock"></i>
                                            No Access
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center" style="padding: 3rem 0;">
                    <i class="fas fa-users-slash" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                    <h5 class="text-muted">No administrators found</h5>
                    <p class="text-muted">Contact system administrator.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    // Form validation
    document.getElementById('addAdminForm')?.addEventListener('submit', function(e) {
        const username = document.querySelector('input[name="username"]').value;
        const password = document.querySelector('input[name="password"]').value;
        const role = document.querySelector('select[name="role"]').value;
        
        if (!username || !password || !role) {
            e.preventDefault();
            alert('Please fill in all required fields.');
            return false;
        }
        
        if (password.length < 6) {
            e.preventDefault();
            alert('Password must be at least 6 characters long.');
            return false;
        }
        
        const usernamePattern = /^[a-zA-Z0-9_]{3,20}$/;
        if (!usernamePattern.test(username)) {
            e.preventDefault();
            alert('Username must be 3-20 characters and contain only letters, numbers, and underscores.');
            return false;
        }
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            alert.style.display = 'none';
        });
    }, 5000);
    </script>
</div>

<?php
// Close prepared statement
$stmt->close();
// Close database connection
$conn->close();
?>