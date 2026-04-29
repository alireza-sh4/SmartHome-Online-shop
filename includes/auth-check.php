<?php

/**
 * requireLogin() - Checks if a user is logged in
 * 
 * How it works:
 * - When a user logs in, we store their ID in $_SESSION['user_id']
 * - If that session variable doesn't exist, they're not logged in
 * - We redirect them to login.php with a flash message
 * 
 * Used on: cart.php, checkout.php, orders.php, profile.php
 */
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['message'] = 'Please log in to access this page.';
        $_SESSION['message_type'] = 'error';
        header('Location: login.php');
        exit;
    }
}

/**
 * requireAdmin() - Checks if the user is an admin
 * 
 * How it works:
 * - First checks if user is logged in (user_id exists)
 * - Then checks if their role is 'admin' (stored in session at login time)
 * - Both conditions must be true, otherwise redirect
 * 
 * Used on: admin.php, admin-products.php, admin-users.php, 
 *          admin-orders.php, admin-logs.php, admin-product-edit.php
 */
function requireAdmin() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        $_SESSION['message'] = 'Access denied. Admin privileges required.';
        $_SESSION['message_type'] = 'error';
        header('Location: login.php');
        exit;
    }
}
?>
