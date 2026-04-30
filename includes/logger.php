<?php

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
