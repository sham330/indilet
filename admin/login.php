<!-- File 1: login.php -->
<?php
session_start();

// If user is already logged in, redirect to admin panel
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: index.php");
    exit();
}

// Include DB connection
include 'connection.php';

$error_message = '';
$success_message = '';

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if (empty($username) || empty($password)) {
        $error_message = "Please enter both username and password.";
    } else {
        // Prepare statement to prevent SQL injection
        $sql = "SELECT id, Username, password, Role FROM admin WHERE Username = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['Username'];
                $_SESSION['admin_role'] = $user['Role'];
                $_SESSION['login_time'] = time();
                
                // Regenerate session ID for security
                session_regenerate_id(true);
                
                // Redirect to admin panel
                header("Location: index.php");
                exit();
            } else {
                $error_message = "Invalid username or password.";
            }
        } else {
            $error_message = "Invalid username or password.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Authentication Required</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-orange: #ff6b35;
            --secondary-orange: #ff8c42;
            --hover-orange: #e55a2b;
            --light-orange: #fff5f2;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
        }
        
        .login-form {
            padding: 60px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .login-header h1 {
            color: #333;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .login-header p {
            color: #666;
            font-size: 16px;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 20px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 0.2rem rgba(255, 107, 53, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(45deg, var(--primary-orange), var(--secondary-orange));
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-size: 16px;
            font-weight: 600;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 53, 0.4);
            color: white;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            padding: 15px 20px;
        }
        
        .login-side {
            background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
            padding: 60px 40px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
        }
        
        .login-side h2 {
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .login-side p {
            font-size: 18px;
            opacity: 0.9;
            line-height: 1.6;
        }
        
        .security-icon {
            font-size: 80px;
            margin-bottom: 30px;
            opacity: 0.8;
        }
        
        .input-group-text {
            background: transparent;
            border: 2px solid #e9ecef;
            border-right: none;
            color: #666;
        }
        
        .input-group .form-control {
            border-left: none;
        }
        
        .input-group:focus-within .input-group-text {
            border-color: var(--primary-orange);
        }
        
        @media (max-width: 768px) {
            .login-form {
                padding: 40px 30px;
            }
            .login-side {
                padding: 40px 30px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="login-container">
                <div class="row g-0">
                    <!-- Login Form Side -->
                    <div class="col-lg-7">
                        <div class="login-form">
                            <div class="login-header">
                                <h1><i class="fas fa-shield-alt me-3" style="color: var(--primary-orange);"></i>Admin Portal</h1>
                                <p>Please sign in to access the admin dashboard</p>
                            </div>

                            <!-- Error/Success Messages -->
                            <?php if (!empty($error_message)): ?>
                                <div class="alert alert-danger" role="alert">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <?php echo htmlspecialchars($error_message); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($success_message)): ?>
                                <div class="alert alert-success" role="alert">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <?php echo htmlspecialchars($success_message); ?>
                                </div>
                            <?php endif; ?>

                            <!-- Login Form -->
                            <form method="post" action="" id="loginForm">
                                <div class="form-group">
                                    <label class="form-label">Username</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-user"></i>
                                        </span>
                                        <input type="text" name="username" class="form-control" 
                                               placeholder="Enter your username" required 
                                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                        <input type="password" name="password" class="form-control" 
                                               placeholder="Enter your password" required id="passwordField">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                            <i class="fas fa-eye" id="passwordToggle"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <button type="submit" name="login" class="btn btn-login">
                                        <i class="fas fa-sign-in-alt me-2"></i>
                                        Sign In
                                    </button>
                                </div>
                            </form>

                            <div class="text-center mt-4">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Contact your system administrator if you need access
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Information Side -->
                    <div class="col-lg-5">
                        <div class="login-side">
                            <div class="security-icon">
                                <i class="fas fa-lock"></i>
                            </div>
                            <h2>Secure Access</h2>
                            <p>Your admin dashboard is protected with enterprise-level security. All sessions are encrypted and monitored.</p>
                            
                            <div class="mt-4">
                                <div class="row text-center">
                                    <div class="col-4">
                                        <i class="fas fa-shield-alt fa-2x mb-2"></i>
                                        <small>Encrypted</small>
                                    </div>
                                    <div class="col-4">
                                        <i class="fas fa-eye-slash fa-2x mb-2"></i>
                                        <small>Private</small>
                                    </div>
                                    <div class="col-4">
                                        <i class="fas fa-clock fa-2x mb-2"></i>
                                        <small>Monitored</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Password visibility toggle
function togglePassword() {
    const passwordField = document.getElementById('passwordField');
    const passwordToggle = document.getElementById('passwordToggle');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        passwordToggle.className = 'fas fa-eye-slash';
    } else {
        passwordField.type = 'password';
        passwordToggle.className = 'fas fa-eye';
    }
}

// Form validation
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const username = document.querySelector('input[name="username"]').value;
    const password = document.querySelector('input[name="password"]').value;
    
    if (!username.trim() || !password.trim()) {
        e.preventDefault();
        alert('Please fill in all fields.');
        return false;
    }
});

// Auto-hide alerts after 5 seconds
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    });
}, 5000);
</script>

</body>
</html>

<?php $conn->close(); ?>
