<?php
// Include authentication check
require_once 'auth_check.php';

// Include DB connection
include 'connection.php';

// Get current user info
$current_user = getCurrentUser();

// Get active tab from URL parameter, default to 'users'
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'users';
$validTabs = ['users', 'listings', 'messages'];

// Validate tab parameter
if (!in_array($activeTab, $validTabs)) {
    $activeTab = 'users';
}

// Page title based on active tab
$pageTitles = [
    'users' => 'Manage Users',
    'listings' => 'Manage Listings', 
    'messages' => 'Manage Messages'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - <?php echo $pageTitles[$activeTab]; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
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

        * {
            box-sizing: border-box;
        }

        body {
            background-color: var(--pure-white);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica Neue', Arial, sans-serif;
            color: var(--text-dark);
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        /* Navigation Styles */
        .navbar {
            background-color: var(--pure-white);
            border-bottom: 1px solid var(--border-light);
            box-shadow: 0 2px 4px rgba(0,0,0,0.04);
            padding: 0.75rem 0;
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        .navbar-brand {
            font-weight: 600;
            font-size: 1.4rem;
            color: var(--text-dark) !important;
            margin-right: 2rem;
        }

        .navbar-brand i {
            color: var(--accent-orange);
            margin-right: 0.5rem;
        }

        .navbar-nav {
            flex-direction: row;
            gap: 0.5rem;
        }

        .nav-link {
            color: var(--text-muted) !important;
            font-weight: 500;
            padding: 0.75rem 1.5rem !important;
            border-radius: 0;
            transition: all 0.2s ease;
            position: relative;
            border-bottom: 3px solid transparent;
            white-space: nowrap;
        }

        .nav-link:hover {
            color: var(--text-dark) !important;
            background-color: transparent;
        }

        .nav-link.active {
            color: var(--text-dark) !important;
            background-color: transparent;
            border-bottom-color: var(--accent-orange);
        }

        .nav-link i {
            margin-right: 0.5rem;
            font-size: 0.9rem;
        }

        .navbar-text {
            color: var(--text-muted) !important;
            font-weight: 500;
            margin-left: auto;
            margin-right: 1rem;
        }

        .navbar-text strong {
            color: var(--text-dark);
        }

        .badge {
            background-color: var(--light-gray);
            color: var(--text-dark);
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-weight: 500;
        }

        .btn-logout {
            background-color: var(--accent-orange);
            border: 1px solid var(--accent-orange);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }

        .btn-logout:hover {
            background-color: #e86b00;
            border-color: #e86b00;
            color: white;
            transform: none;
        }

        /* Main Container */
        .main-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        /* Card Styles */
        .admin-card {
            background: var(--pure-white);
            border: 1px solid var(--border-light);
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            overflow: hidden;
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

        /* User Info Card */
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
        }

        /* Loading Spinner */
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 3rem;
        }

        .loading-spinner .spinner-border {
            color: var(--accent-orange);
            width: 2.5rem;
            height: 2.5rem;
        }

        /* Button Styles */
        .btn-primary {
            background-color: var(--accent-orange);
            border-color: var(--accent-orange);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background-color: #e86b00;
            border-color: #e86b00;
            color: white;
        }

        .btn-success {
            background-color: #198754;
            border-color: #198754;
            border-radius: 6px;
        }

        .btn-success:hover {
            background-color: #157347;
            border-color: #157347;
        }

        .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
            color: var(--text-dark);
            border-radius: 6px;
        }

        .btn-warning:hover {
            background-color: #ffcd3b;
            border-color: #ffcd3b;
            color: var(--text-dark);
        }

        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
            border-radius: 6px;
        }

        .btn-danger:hover {
            background-color: #bb2d3b;
            border-color: #bb2d3b;
        }

        .btn-outline-secondary {
            color: var(--text-muted);
            border-color: var(--border-light);
            border-radius: 6px;
        }

        .btn-outline-secondary:hover {
            background-color: var(--light-gray);
            color: var(--text-dark);
            border-color: var(--border-light);
        }

        /* Alert Styles */
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
            background-color: #cff4fc;
            color: #055160;
            border-left-color: #0dcaf0;
        }

        /* Form Styles */
        .form-control {
            border: 1px solid var(--border-light);
            border-radius: 6px;
            padding: 0.5rem 0.75rem;
            transition: border-color 0.2s ease;
            background-color: var(--pure-white);
        }

        .form-control:focus {
            border-color: var(--accent-orange);
            box-shadow: 0 0 0 0.2rem rgba(253, 126, 20, 0.15);
        }

        .form-label {
            color: var(--text-dark);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        /* Table Styles */
        .table {
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 0;
        }

        .table thead th {
            background-color: var(--light-gray);
            border-bottom: 1px solid var(--border-light);
            color: var(--text-dark);
            font-weight: 600;
            padding: 1rem 0.75rem;
            border-top: none;
        }

        .table tbody td {
            padding: 0.75rem;
            border-top: 1px solid var(--border-light);
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background-color: rgba(253, 126, 20, 0.03);
        }

        /* Modal Styles */
        .modal-content {
            border-radius: 8px;
            border: 1px solid var(--border-light);
        }

        .modal-header {
            background-color: var(--light-gray);
            border-bottom: 1px solid var(--border-light);
            border-radius: 8px 8px 0 0;
        }

        .modal-title {
            color: var(--text-dark);
            font-weight: 600;
        }

        /* Stats Card */
        .stats-card {
            background: var(--pure-white);
            border: 1px solid var(--border-light);
            border-radius: 6px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: box-shadow 0.2s ease;
        }

        .stats-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .stats-card h5 {
            color: var(--text-dark);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .navbar-nav {
                flex-direction: column;
                gap: 0;
                width: 100%;
                margin-top: 1rem;
                padding-top: 1rem;
                border-top: 1px solid var(--border-light);
            }

            .nav-link {
                padding: 0.75rem 0 !important;
                border-bottom: none;
                border-left: 3px solid transparent;
            }

            .nav-link.active {
                border-bottom-color: transparent;
                border-left-color: var(--accent-orange);
            }

            .navbar-text {
                margin: 1rem 0;
                text-align: center;
            }

            .btn-logout {
                align-self: center;
                margin-top: 0.5rem;
            }
        }

        @media (max-width: 768px) {
            .main-container {
                margin: 1rem auto;
                padding: 0 0.5rem;
            }

            .card-header {
                padding: 1rem;
            }

            .card-body {
                padding: 1rem;
            }

            .user-info-card {
                padding: 1rem;
            }

            .navbar-brand {
                font-size: 1.2rem;
            }
        }

        @media (max-width: 576px) {
            .card-header h4 {
                font-size: 1.1rem;
            }

            .btn {
                font-size: 0.875rem;
                padding: 0.5rem 0.75rem;
            }

            .table {
                font-size: 0.875rem;
            }

            .table thead th,
            .table tbody td {
                padding: 0.5rem;
            }
        }

        /* Utility Classes */
        .text-muted {
            color: var(--text-muted) !important;
        }

        .bg-light {
            background-color: var(--light-gray) !important;
        }

        /* Animation */
        .fade-in {
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from { 
                opacity: 0; 
                transform: translateY(10px); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0); 
            }
        }

        /* Focus styles for accessibility */
        .nav-link:focus,
        .btn:focus,
        .form-control:focus {
            outline: 2px solid var(--accent-orange);
            outline-offset: 2px;
        }

        /* High contrast for better readability */
        .navbar-toggler {
            border: 1px solid var(--border-light);
            padding: 0.5rem;
        }

        .navbar-toggler:focus {
            box-shadow: 0 0 0 0.2rem rgba(253, 126, 20, 0.25);
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%2833, 37, 41, 0.75%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-shield-alt"></i>
                Admin Panel
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $activeTab === 'users' ? 'active' : ''; ?>" 
                           href="?tab=users" aria-current="<?php echo $activeTab === 'users' ? 'page' : 'false'; ?>">
                            <i class="fas fa-users"></i>
                            Manage Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $activeTab === 'listings' ? 'active' : ''; ?>" 
                           href="?tab=listings" aria-current="<?php echo $activeTab === 'listings' ? 'page' : 'false'; ?>">
                            <i class="fas fa-list-ul"></i>
                            Manage Listings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $activeTab === 'messages' ? 'active' : ''; ?>" 
                           href="?tab=messages" aria-current="<?php echo $activeTab === 'messages' ? 'page' : 'false'; ?>">
                            <i class="fas fa-envelope"></i>
                            Manage Messages
                        </a>
                    </li>
                </ul>
                
                <div class="d-flex align-items-center flex-column flex-lg-row">
                    <span class="navbar-text">
                        <i class="fas fa-user-circle me-1"></i>
                        Welcome, <strong><?php echo htmlspecialchars($current_user['Username']); ?></strong>
                        <span class="badge ms-2"><?php echo ucfirst($current_user['Role']); ?></span>
                    </span>
                    <a href="logout.php" class="btn btn-logout">
                        <i class="fas fa-sign-out-alt me-1"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="main-container">
        <!-- Admin Panel Card -->
        <div class="card admin-card fade-in">
            <!-- Card Header -->
            <div class="card-header">
                <h4>
                    <i class="fas fa-cogs"></i>
                    <?php echo $pageTitles[$activeTab]; ?>
                </h4>
            </div>

            <!-- Card Body -->
            <div class="card-body">
                <!-- User Info Card -->
                <div class="user-info-card">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6>
                                <i class="fas fa-info-circle"></i>
                                Current Session Information
                            </h6>
                            <p class="mb-0">
                                Logged in as: <strong><?php echo htmlspecialchars($current_user['Username']); ?></strong>
                                <span class="badge ms-2"><?php echo ucfirst($current_user['Role']); ?></span>
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end mt-2 mt-md-0">
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>
                                <?php echo date('Y-m-d H:i:s'); ?>
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Loading Spinner -->
                <div class="loading-spinner" id="loadingSpinner">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Loading content...</p>
                </div>

                <!-- Content Area -->
                <div class="content-area" id="contentArea">
                    <?php
                    // Include the appropriate management file based on active tab
                    switch($activeTab) {
                        case 'users':
                            if (file_exists('register_admin.php')) {
                                include 'register_admin.php';
                            } else {
                                echo '<div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <strong>Notice:</strong> User management module (manageuser.php) not found.
                                      </div>';
                            }
                            break;
                            
                        case 'listings':
                            if (file_exists('listings.php')) {
                                include 'listings.php';
                            } else {
                                echo '<div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <strong>Notice:</strong> Listings management module (managelistings.php) not found.
                                      </div>';
                            }
                            break;
                            
                        case 'messages':
                            if (file_exists('managemessages.php')) {
                                include 'managemessages.php';
                            } else {
                                echo '<div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <strong>Notice:</strong> Messages management module (managemessages.php) not found.
                                      </div>';
                            }
                            break;
                            
                        default:
                            echo '<div class="alert alert-danger">
                                    <i class="fas fa-times-circle me-2"></i>
                                    <strong>Error:</strong> Invalid tab selected.
                                  </div>';
                            break;
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Enhanced navigation and UX
        document.addEventListener('DOMContentLoaded', function() {
            const navLinks = document.querySelectorAll('.nav-link');
            const loadingSpinner = document.getElementById('loadingSpinner');
            const contentArea = document.getElementById('contentArea');
            
            // Navigation loading effect
            navLinks.forEach(function(link) {
                if (!link.classList.contains('active')) {
                    link.addEventListener('click', function(e) {
                        // Show loading spinner
                        if (loadingSpinner && contentArea) {
                            loadingSpinner.style.display = 'block';
                            contentArea.style.opacity = '0.5';
                            
                            // Simulate loading time
                            setTimeout(function() {
                                loadingSpinner.style.display = 'none';
                                contentArea.style.opacity = '1';
                            }, 300);
                        }
                    });
                }
            });
            
            // Auto-hide alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    if (alert && alert.parentNode) {
                        alert.style.transition = 'opacity 0.5s ease-out';
                        alert.style.opacity = '0';
                        setTimeout(function() {
                            if (alert.parentNode) {
                                alert.remove();
                            }
                        }, 500);
                    }
                }, 5000);
            });
            
            // Auto-collapse mobile navbar when clicking on nav links
            const navbarToggler = document.querySelector('.navbar-toggler');
            const navbarCollapse = document.querySelector('.navbar-collapse');
            
            if (navbarToggler && navbarCollapse) {
                navLinks.forEach(function(link) {
                    link.addEventListener('click', function() {
                        if (window.innerWidth < 992 && navbarCollapse.classList.contains('show')) {
                            navbarToggler.click();
                        }
                    });
                });
            }

            // Enhanced form validation
            const forms = document.querySelectorAll('form');
            forms.forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    const requiredFields = form.querySelectorAll('[required]');
                    let isValid = true;
                    
                    requiredFields.forEach(function(field) {
                        if (!field.value.trim()) {
                            field.classList.add('is-invalid');
                            isValid = false;
                        } else {
                            field.classList.remove('is-invalid');
                        }
                    });
                    
                    if (!isValid) {
                        e.preventDefault();
                        alert('Please fill in all required fields.');
                    }
                });
            });

            // Smooth scrolling for better UX
            document.documentElement.style.scrollBehavior = 'smooth';
        });

        // Confirmation dialog for dangerous actions
        function confirmAction(message = 'Are you sure you want to perform this action?') {
            return confirm(message);
        }

        // Enhanced loading state for forms
        function addLoadingState(form) {
            const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
                
                // Reset after 3 seconds if form doesn't redirect
                setTimeout(function() {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }, 3000);
            }
        }

        // Initialize tooltips if needed
        if (typeof bootstrap !== 'undefined') {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    </script>
</body>
</html>
