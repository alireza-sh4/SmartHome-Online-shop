<?php

/**
 * logAction() - Records a user action
 * 
 * @param PDO    $pdo     - Database connection object
 * @param int    $userId  - ID of the user performing the action
 * @param string $action  - Action type (e.g., 'LOGIN', 'ORDER_PLACED', 'LOGOUT')
 * @param string $details - Additional info (e.g., 'Order #5 placed, total: €149.99')
 */
function logAction($pdo, $userId, $action, $details = '') {
    $timestamp = date('Y-m-d H:i:s');
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    $logFile = $logDir . '/activity.txt';
    $logEntry = "[$timestamp] User ID: $userId | Action: $action | Details: $details" . PHP_EOL;
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO logs (user_id, action, details, created_at) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $action, $details, $timestamp]);
    } catch (PDOException $e) {
        error_log("Database logging failed: " . $e->getMessage());
    }
}
?>
