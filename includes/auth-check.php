<?php


function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['message'] = 'Please log in to access this page.';
        $_SESSION['message_type'] = 'error';
        header('Location: login.php');
        exit;
    }
}

function requireAdmin() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        $_SESSION['message'] = 'Access denied. Admin privileges required.';
        $_SESSION['message_type'] = 'error';
        header('Location: login.php');
        exit;
    }
}
?>
