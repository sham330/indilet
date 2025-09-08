<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Check session timeout (optional - 2 hours)
$timeout_duration = 7200; // 2 hours in seconds
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=1");
    exit();
}

// Update last activity time
$_SESSION['login_time'] = time();

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Function to check if user has specific role
function hasRole($required_role) {
    return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === $required_role;
}

// Function to check if user is admin
function isAdmin() {
    return hasRole('admin');
}

// Function to get current user info
function getCurrentUser() {
    return [
        'id' => $_SESSION['admin_id'] ?? null,
        'Username' => $_SESSION['admin_username'] ?? null, // Note: Capital 'U' to match your index.php usage
        'Role' => $_SESSION['admin_role'] ?? null          // Note: Capital 'R' to match your index.php usage
    ];
}
?>