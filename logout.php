<?php

require_once 'includes/db-connection.php';
require_once 'includes/logger.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['user_id'])) {
    logAction($pdo, $_SESSION['user_id'], 'LOGOUT', 'User logged out');
}
session_unset();
session_destroy();
session_start();
$_SESSION['message'] = 'You have been logged out successfully.';
$_SESSION['message_type'] = 'info';
header('Location: index.php');
exit;
?>
